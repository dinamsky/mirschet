<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.2.0
 * @copyright Serge Rodovnichenko, 2017
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
class shopSdekintPluginCitiesActions extends waJsonActions
{
    /** @var shopSdekintPluginCityModel */
    protected $City;

    protected function preExecute()
    {
        parent::preExecute();
        $this->City = new shopSdekintPluginCityModel();
    }

    /**
     * @throws waException
     */
    public function viewAction()
    {
        $code = $this->getRequest()->get('code');
        if (!$code) {
            throw new waException('Bad Request', 400);
        }

        $this->response = $this->City->getCityByCode($code);

    }

    public function indexAction()
    {
        $term = waRequest::get('term');
        $country = waRequest::get('country');
        $region = waRequest::get('region');
        $with_stock_only = (bool)$this->getRequest()->get('with_stocks', 0);

        $Country = new waCountryModel();
        $Region = new waRegionModel();

        $conditions = array('`sspc`.`name` LIKE s:name');
        $values = array('name' => $this->City->escape($term, 'like') . '%');

        $fields = array('`sspc`.*, `sspc`.`sdek_id` AS `value`');
        $tables = array("`{$this->City->getTableName()}` AS `sspc`");

        if ($country) {
            $conditions[] = '`sspc`.`country_iso3`=s:country';
            $values['country'] = $country;
        }
        if ($region) {
            $conditions[] = '`sspc`.`region_code`=s:region';
            $values['region'] = $region;
        }

        if($with_stock_only) {
            $tables[] = 'INNER JOIN `shop_sdekint_pvz` AS `ssp` ON `sspc`.`sdek_id` = `ssp`.`city_code`';
        }

        $conditions = implode(' AND ', $conditions);
        $fields = implode(', ', $fields);
        $tables = implode(' ', $tables);

        $sql = "SELECT $fields FROM $tables WHERE $conditions GROUP BY `sdek_id` ORDER BY `is_center` DESC, `name` ASC";

        $result = $this->City->query($sql, $values)->fetchAll();
        array_walk($result, function (&$c) use ($Country, $Region) {
            $c['Country'] = $Country->get($c['country_iso3']);
            $c['Region'] = $Region->get($c['country_iso3'], $c['region_code']);
            switch ($c['value']) {
                case '44' :
                case '137':
                case '15256':
                    $c['label'] = $c['name'];
                    break;
                default:
                    if ($c['Region'] && !empty($c['Region'] && !empty($c['Region']['name']))) {
                        $c['label'] = "{$c['name']}, {$c['Region']['name']}";
                    } elseif (!empty($c['area'])) {
                        $c['label'] = "{$c['name']}, {$c['area']}";
                    } else {
                        $c['label'] = "{$c['name']}";
                    }
                    break;
            }
            $c = shopSdekintPluginHelper::typecastScalarArrayValues(
                $c,
                [
                    'pod_max'   => ['type' => 'float', 'null' => true],
                    'is_center' => 'bool',
                    'id'        => 'int'
                ]
            );
        });

        $this->response = $result;
    }
}
