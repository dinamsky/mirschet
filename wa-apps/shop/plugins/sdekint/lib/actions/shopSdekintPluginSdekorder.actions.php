<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.3.0
 * @copyright Serge Rodovnichenko, 2015
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint
 */
class shopSdekintPluginSdekorderActions extends waJsonActions
{
    /** @var shopOrderModel */
    protected $Order;

    /** @var shopSdekintPlugin */
    protected $plugin;

    /**
     * @throws waException
     * @throws waRightsException
     */
    protected function preExecute()
    {
        if (!wa()->getUser()->getRights('shop', 'orders')) {
            throw new waRightsException;
        }

        $this->Order = new shopOrderModel();
        $this->getResponse()->addHeader('Content-type', 'application/json');
        $this->plugin = wa('shop')->getPlugin('sdekint');

        parent::preExecute();
    }

    /**
     * @todo: Check order_id and order existence
     */
    public function sendAction()
    {
        $req = $this->getRequest()->post('order');
        $id = $this->getRequest()->post('id');
        if (!$req || !$id) {
            $this->errors[] = 'Неверный формат запроса';
            return;
        }

        $data = shopHelper::jsonDecode($req, true);
        if (is_null($data) || !is_array($data)) {
            $this->errors[] = 'Неверный формат запроса';
            return;
        }

        $courier_call = $this->extractCourierCall($data['origin']);

        $workflow = new shopWorkflow();
        /** @var shopSdekintPluginSdekSendAction $action */
        $action = $workflow->getActionById('sdek-send');

        try {
            $result = $action->run(array('order_id' => $id, 'order' => $data));
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        if (is_null($result)) {
            shopSdekintPluginHelper::log("Error sending DeliveryRequest:\n" . var_export($action->errors, true));
            $this->errors = $action->errors;
            return;
        }

        $this->response = $result;

        return;
    }

    /**
     * @throws waException
     */
    public function indexAction()
    {
        $valid_types = ['last_week', 'last_month', 'last_three_months', 'custom'];
        $conditions = $this->getRequest()->get('filter', ['type' => 'last_week'], waRequest::TYPE_ARRAY);
        if (!is_array($conditions) || !array_key_exists('type', $conditions) || !in_array($conditions['type'], $valid_types)) {
            throw new waException('Bad Request', 400);
        }

        $net = new waNet(['format' => waNet::FORMAT_XML, 'request_format' => waNet::FORMAT_RAW]);

        $xmlReq = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><InfoRequest />');
        $xmlReq['Account'] = $this->plugin->getSettings('api_auth');

        $date_first = new DateTimeImmutable();
        $xmlReq['Date'] = $date_first->format('c');
        $xmlReq['Secure'] = shopSdekintPluginHelper::getSecureKey($date_first, $this->plugin->getSettings('api_key'));

        if ($conditions['type'] == 'custom') {
            $date_first = DateTimeImmutable::createFromFormat('Y-m-d', $conditions['date_beg']);
        } else {
            switch ($conditions['type']) {
                case 'last_week':
                    $date_first = $date_first->sub(new DateInterval('P1W'));
                    break;
                case 'last_month':
                    $date_first = $date_first->sub(new DateInterval('P1M'));
                    break;
                case 'last_three_months':
                    $date_first = $date_first->sub(new DateInterval('P3M'));
                    break;
            }
        }

        $xmlReq->ChangePeriod['DateBeg'] = $date_first->format('Y-m-d');

        //http://int.cdek.ru/info_report.php
        //http://int.cdek.ru/status_report_h.php
        $result = $net->query('http://int.cdek.ru/info_report.php', ['xml_request' => $xmlReq->asXML()], waNet::METHOD_POST);

        foreach ($result->Order as $o)
            waLog::log($o->asXML(), 'xml.log');


        $this->response = [];
    }

    /*
     * array (
  'origin' =>
  array (
    'sender' =>
    array (
      'name' => 'Интернет-магазин',
    ),
    'contact' =>
    array (
      'name' => '',
      'phone' => '',
    ),
    'address' =>
    array (
      'city' =>
      array (
        'name' => 'Москва',
        'id' => '44',
      ),
      'street' => '',
      'house' => '',
      'flat' => '',
    ),
    'courier' =>
    array (
      'date' => '2018-05-27',
      'time_beg' => '9:00',
      'time_end' => '18:00',
    ),
    'type' => 'from-stock',
  ),
)
     */
    private function extractCourierCall($origin)
    {
        if ($origin['type'] !== 'from-door') {
            return null;
        }

        $call = array(
            'date'           => $origin['courier']['date'],
            'time_beg'       => $origin['courier']['time_beg'],
            'time_end'       => $origin['courier']['time_end'],
            'send_city_code' => $origin['address']['city']['id'],
            'send_phone'     => $origin['contact']['phone'],
            'sender_name'      => $origin['contact']['name'],
            'address'        => array(
                'street' => $origin['address']['street'],
                'house'  => $origin['address']['house'],
                'flat'   => $origin['address']['flat'],
            )
        );

        return $call;
    }
}
