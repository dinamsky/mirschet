<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 4.0.0
 * @copyright Serge Rodovnichenko, 2015-2016
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint.model
 */

use SergeR\CakeUtility\Exception\XmlException;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SergeR\Webasyst\CdekSDK\API\Order\Response\PvzListResponse;
use SergeR\Webasyst\CdekSDK\Type\Pvz;
use SergeR\Webasyst\CdekSDK\Type\WorkTimeY;

/**
 * Pickup point model
 */
class shopSdekintPluginPvzModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_sdekint_pvz';

    /**
     * @param $code
     * @return array|null
     * @throws waException
     */
    public function getByCode($code)
    {
        return $this->getByField('code', $code);
    }

    /**
     * @param array $conditions
     * @return array
     */
    public function find(array $conditions = array())
    {
        return (array)$this->findDbQuery($conditions)->fetchAll();
    }

    /**
     * @param array $conditions
     * @return waDbResultSelect
     */
    public function findQuery(array $conditions = array())
    {
        return $this->findDbQuery($conditions)->query();
    }

    /**
     * @param array $conditions
     * @return waDbQuery
     */
    protected function findDbQuery(array $conditions = array())
    {
        $where_chunks = array();
        $where_values = array();

        foreach ($conditions as $con => $val) {
            switch ($con) {
                case 'point_type' :
                    $where_chunks[] = '`point_type`=:type';
                    $where_values['type'] = $val;
                    break;
                case 'weight':
                    $where_chunks[] = '(ISNULL(`min_weight`) OR (`min_weight` <= :weight)) AND (ISNULL(`max_weight`) OR `max_weight` >= :weight)';
                    $where_values['weight'] = $val;
                    break;
                default:
                    if ($this->fieldExists($con)) {
                        $where_chunks[] = "`$con`=:$con";
                        $where_values[$con] = $val;
                    }
            }
        }

        $select = $this->select('*');
        if (!empty($where_chunks) && !empty($where_values)) {
            $where = implode(' AND ', $where_chunks);
            $select = $select->where($where, $where_values);
        }

        $points = $select->order('name');
        return $points;
    }

    /**
     * @param SimpleXMLElement $data
     * @throws waException
     * @deprecated
     */
    public function loadFromXml($data)
    {
        if (!$data) {
            shopSdekintPluginHelper::log("В XML не найдено ни одного ПВЗ");
            throw new waException('Ошибка разбора списка ПВЗ');
        }

        $this->truncate();

        foreach ($data as $pvz) {
            if (mb_strtolower((string)$pvz['Name']) == 'фиктивный') {
                continue;
            }
            $row = array(
                'code'              => (string)$pvz['Code'],
                'name'              => (string)$pvz['Name'],
                'city_code'         => intval((string)$pvz['CityCode']),
                'city'              => (string)$pvz['City'],
                'work_time'         => (string)$pvz['WorkTime'],
                'address'           => (string)$pvz['Address'],
                'phone'             => (string)$pvz['Phone'],
                'note'              => (string)$pvz['Note'],
                'coord_x'           => floatval(str_replace(',', '.', (string)$pvz['coordX'])),
                'coord_y'           => floatval(str_replace(',', '.', (string)$pvz['coordY'])),
                'owner'             => (string)$pvz['ownerCode'],
                'point_type'        => (string)$pvz['Type'],
                'min_weight'        => is_null($pvz->WeightLimit['WeightMin']) ?
                    null :
                    floatval(str_replace(',', '.', (string)$pvz->WeightLimit['WeightMin'])),
                'max_weight'        => is_null($pvz->WeightLimit['WeightMax']) ?
                    null :
                    floatval(str_replace(',', '.', (string)$pvz->WeightLimit['WeightMax'])),
                'country_name_sdek' => (string)$pvz['CountryName'],
                'country_code_sdek' => (string)$pvz['CountryCode'],
                'region_code_sdek'  => (string)$pvz['RegionCode'],
                'region_name_sdek'  => (string)$pvz['RegionName'],
                'full_address'      => (string)$pvz['FullAddress'],
                'dressing_room'     => shopSdekintPluginHelper::boolval((string)$pvz['IsDressingRoom']) ? 1 : 0,
                'have_cashless'     => shopSdekintPluginHelper::boolval((string)$pvz['HaveCashless']) ? 1 : 0,
                'allowed_cod'       => shopSdekintPluginHelper::boolval((string)$pvz['AllowedCod']) ? 1 : 0,
                'nearest_station'   => (string)$pvz['NearestStation'],
                'metro'             => (string)$pvz['MetroStation'],
                'site'              => (string)$pvz['Site']
            );

            $raw_data = array('work_time_y' => [], 'office_image' => [], 'office_how_go' => []);
            try {
                $raw_data['work_time_y'] = $this->extractWorkTimeY($pvz);
            } catch (XmlException $e) {
                shopSdekintPluginHelper::log('Ошибка разбора XML с ПВЗ (WorkTimeY):' . $e->getMessage());
            }

            if (count($pvz->OfficeImage)) {
                foreach ($pvz->OfficeImage as $oi) {
                    $raw_data['office_image'][] = $oi['url'];
                }
            }

            if (count($pvz->OfficeHowGo)) {
                foreach ($pvz->OfficeHowGo as $ohg) {
                    $raw_data['office_how_go'][] = $ohg['url'];
                }
            }

            $row['raw_data'] = json_encode($raw_data);

            try {
                $this->insert($row);
            } catch (Exception $e) {
                shopSdekintPluginHelper::log(
                    "Ошибка сохранения данных ПВЗ (" . $e->getMessage() . "): \n" . var_export($row, true)
                );
            }
        }
    }

    /**
     * @param PvzListResponse $data
     * @return int Количество добавленных записей
     * @throws waException
     */
    public function loadFromResponse(PvzListResponse $data)
    {
        if (!$data->count()) {
            shopSdekintPluginHelper::log("В XML не найдено ни одного ПВЗ");
            throw new waException('Ошибка разбора списка ПВЗ');
        }

        $this->truncate();

        $rows = [];
        $total = 0;

        /** @var Pvz $pvz */
        foreach ($data as $pvz) {
            if (mb_strtolower($pvz->getName()) === 'фиктивный') {
                continue;
            }
            $row = array(
                'code'              => $pvz->getCode(),
                'name'              => $pvz->getName(),
                'city_code'         => $pvz->getCityCode(),
                'city'              => $pvz->getCity(),
                'work_time'         => $pvz->getWorkTime(),
                'address'           => $pvz->getAddress(),
                'phone'             => $pvz->getPhone(),
                'note'              => $pvz->getNote(),
                'coord_x'           => $pvz->getCoordX(),
                'coord_y'           => $pvz->getCoordY(),
                'owner'             => $pvz->getOwnerCode(),
                'point_type'        => $pvz->getType(),
                'min_weight'        => ($pvz->getWeightLimit() === null ? null : $pvz->getWeightLimit()->getWeightMinValue('kg')),
                'max_weight'        => ($pvz->getWeightLimit() === null ? null : $pvz->getWeightLimit()->getWeightMaxValue('kg')),
                'country_name_sdek' => $pvz->getCountryName(),
                'country_code_sdek' => $pvz->getCountryCode(),
                'region_code_sdek'  => $pvz->getRegionCode(),
                'region_name_sdek'  => $pvz->getRegionName(),
                'full_address'      => $pvz->getFullAddress(),
                'dressing_room'     => (int)$pvz->isDressingRoom(),
                'have_cashless'     => (int)$pvz->isHaveCashless(),
                'allowed_cod'       => (int)$pvz->isAllowedCod(),
                'nearest_station'   => $pvz->getNearestStation(),
                'metro'             => $pvz->getMetroStation(),
                'site'              => $pvz->getSite()
            );

            $raw_data = array('work_time_y' => [], 'office_image' => [], 'office_how_go' => []);

            /** @var WorkTimeY $item */
            foreach ($pvz->getWorkTimeY() as $item) {
                if ($item->getDay() && $item->getPeriods()) {
                    $raw_data['work_time_y'][] = ['day' => $item->getDay(), 'periods' => $item->getPeriods()];
                }
            }

            if (count($pvz->getOfficeImage())) {
                foreach ($pvz->getOfficeImage() as $oi) {
                    $raw_data['office_image'][] = is_array($oi) ? Hash::get($oi, '@url') : $oi;
                }
            }
            $raw_data['office_image'] = array_filter($raw_data['office_image']);

            if (count($pvz->getOfficeHowGo())) {
                foreach ($pvz->getOfficeHowGo() as $ohg) {
                    $raw_data['office_how_go'][] = $ohg['url'];
                }
            }

            $row['raw_data'] = json_encode($raw_data);

            $rows[] = $row;

            if(count($rows) % 10 == 0) {
                try {
                    $this->multipleInsert($rows);
                    $total += 10;
                } catch (Exception $e) {
                    shopSdekintPluginHelper::log('Исключение при сохранении списка ПВЗ: ' . $e->getMessage(), true);
                }
                $rows = [];
            }
        }

        if($rows) {
            try {
                $this->multipleInsert($rows);
                $total += count($rows);
            } catch (Exception $e) {
                shopSdekintPluginHelper::log('Исключение при сохранении списка ПВЗ: ' . $e->getMessage(), true);
            }
        }

        return $total;
    }

    /**
     * @param SimpleXMLElement $x
     * @return array
     * @throws XmlException
     */
    protected function extractWorkTimeY($x)
    {
        if (!count($x->WorkTimeY)) {
            return [];
        }

        $result = array();
        foreach ($x->WorkTimeY as $wty) {
            $w = Xml::toArray($wty);
            $r = array('day' => ifset($w, 'WorkTimeY', '@day', ''), 'periods' => ifset($w, 'WorkTimeY', '@periods', ''));
            if (!empty($r['day']) && !empty($r['periods'])) {
                $result[] = $r;
            }
        }

        return $result;
    }


}
