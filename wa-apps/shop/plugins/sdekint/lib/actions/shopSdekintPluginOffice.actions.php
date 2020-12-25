<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2017
 * @license
 */
class shopSdekintPluginOfficeActions extends waJsonActions
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /** @var shopSdekintPluginPvzModel */
    protected $Office;

    /**
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();
        $this->plugin = wa('shop')->getPlugin('sdekint');
        $this->Office = new shopSdekintPluginPvzModel();
    }

    public function defaultAction()
    {
        $view = wa()->getView();
        $this->response['html'] = $view->fetch($this->getTemplate('Office'));
    }

    /**
     * Поиск городов, в которых есть ПВЗ
     * Надо бы это безобразие в модель унести
     */
    public function citiesAction()
    {
        $this->response = [];
        $condition_keys = $condition_values = array();

        $country_iso3 = $this->getRequest()->get('country');
        if ($country_iso3) {
            $condition_keys[] = 'wr.country_iso3=s:country';
            $condition_values['country'] = $country_iso3;
        }

        $term = $this->getRequest()->get('term');
        if ($term) {
            $condition_keys[] = 'ssc.name LIKE "l:term%"';
            $condition_values['term'] = $term;
        }

        if (!empty($condition_keys)) {
            $sql = 'SELECT DISTINCT ssp.city_code AS value, CONCAT_WS(", ", ssc.name, ssc.area) as label ' .
                'FROM shop_sdekint_pvz AS ssp ' .
                'INNER JOIN shop_sdekint_cities AS ssc ON ssp.city_code=ssc.sdek_id ' .
                'LEFT JOIN wa_region AS wr ON (ssc.region_code=wr.code AND ssc.country_iso3=wr.country_iso3) ' .
                'WHERE ' . implode(' AND ', $condition_keys) .
                ' ORDER BY ssc.is_center DESC, ssc.name ASC ' .
                'LIMIT 0, 15';

            $result = (array)$this->Office->query($sql, $condition_values)->fetchAll();
            $this->response = array_values($result);
        }
    }

    /**
     * Список ПВЗ
     *
     * @throws waException
     */
    public function indexAction()
    {
        // пока только все ПВЗ в городе умеем показывать
        $city_code = $this->getRequest()->get('city_code', 0, waRequest::TYPE_INT);
        $condition_keys = $conditions_values = $count_conditions = array();
        if ($city_code) {
            $condition_keys[] = 'city_code=i:city_code';
            $conditions_values['city_code'] = $city_code;
            $count_conditions['city_code'] = $city_code;
        }

        $count_conditions['point_type'] = 'PVZ';
        $condition_keys[] = 'point_type=s:point_type';
        $conditions_values['point_type'] = 'PVZ';

        if (empty($count_conditions)) { // просто список пока нельзя
            throw new waException('Bad request', 400);
        }

        $this->response = ['office_list' => []];;

        $no_limit = (bool)$this->getRequest()->get('nolimit', 0, waRequest::TYPE_INT);
        $total = $this->Office->countByField($count_conditions);
        $pagination = $this->getPagination($total);

        if ($total) {
            $offices = $this->Office
                ->select('*')
                ->where(implode(' AND ', $condition_keys), $conditions_values)
                ->order('name');

            if (!$no_limit) {
                $this->response['pagination'] = $pagination;
                $offices = $offices->limit("{$pagination['start']}, {$pagination['count']}");
            }

            $office_list = array_values((array)$offices->fetchAll());
            array_walk($office_list, function (&$o) {
                $o = shopSdekintPluginHelper::typecastScalarArrayValues(
                    $o,
                    array(
                        'coord_x'    => 'float',
                        'coord_y'    => 'float',
                        'max_weight' => ['type' => 'float', 'null' => true],
                        'min_weight' => ['type' => 'float', 'null' => true]
                    )
                );
            });

            $this->response['office_list'] = $office_list;
        }
    }

    /**
     * Обработка входных параметров запроса. todo DRY!
     *
     * @param int $total
     * @return array
     */
    protected function getPagination($total = 0)
    {
        $rows_on_page = (int)$this->getConfig()->getOption('products_per_page');
        $rows_on_page = $rows_on_page > 2 ? $rows_on_page : 30;
        $page = (int)$this->getRequest()->request('page', 0, waRequest::TYPE_INT);
        $page = $page > 0 ? $page : 1;

        $pages = (int)ceil($total / $rows_on_page);
        if ($pages < 1) {
            $pages = 1;
        }
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = $rows_on_page * ($page - 1);

        return ['start' => $offset, 'count' => $rows_on_page, 'page' => $page, 'pages' => $pages, 'total' => $total];
    }

    protected function getTemplate($template = '')
    {
        $path = 'plugins/sdekint/templates/actions/backend';
        if ($template) {
            $path .= '/' . $template . '.html';
        }
        return wa()->getAppPath($path, 'shop');
    }
}
