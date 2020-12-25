<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 1.0.0
 * @copyright Serge Rodovnichenko, 2015
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint.controller
 */
class shopSdekintPluginStocksGetController extends waJsonController
{
    private $stock_map = array(
        'postamat' => 'POSTOMAT',
        'stock'    => 'PVZ'
    );

    public function execute()
    {
        $Point = new shopSdekintPluginPvzModel();

        $stocks = $conditions = array();
        $city_id = waRequest::get('city_id', 0, waRequest::TYPE_INT);
        if ($city_id) {
            $conditions['city_code'] = $city_id;
        }

        $type_req = waRequest::get('stock_type', null, waRequest::TYPE_STRING_TRIM);
        $weight = floatval(str_replace(',', '.', waRequest::get('weight', '0', waRequest::TYPE_STRING_TRIM)));

        if (!is_null($type_req) && array_key_exists($type_req, $this->stock_map)) {
            $conditions['point_type'] = $this->stock_map[$type_req];
        }

        if ($weight > 0) {
            $conditions['weight'] = $weight;
        }

        $this->response['stocks'] = $Point->find($conditions);
    }
}
