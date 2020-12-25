<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.1.0
 * @copyright Serge Rodovnichenko, 2015
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint.api
 */

/**
 *
 */
class shopSdekintPluginSdekApi extends waNet
{
    public $errors = array();
    /** @var string */
    private $authLogin;
    /** @var string */
    private $secretKey;
    private $api_url = 'https://integration.cdek.ru/';

    public function __construct($options = array(), $custom_headers = array())
    {
        if (!empty($options['auth_login'])) {
            $this->setAuthLogin($options['auth_login']);
        }

        if (!empty($options['secret_key'])) {
            $this->setSecretKey($options['secret_key']);
        }

        unset($options['auth_login'], $options['secret_key']);

//        shopSdekintPluginHelper::checkRequiredExtensions();
        parent::__construct($options, $custom_headers);

        $this->user_agent = sprintf(
            'CDEK Integration Plugin v.%s for Webasyst-Framework/%s',
            wa('shop')->getPlugin('sdekint')->getVersion(),
            wa()->getVersion('webasyst')
        );
        $this->options['log'] = 'shop/sdekint.log';
    }

    public function setAuthLogin($auth_login)
    {
        $this->authLogin = $auth_login;
    }

    public function setSecretKey($secret_key)
    {
        $this->secretKey = $secret_key;
    }

    public static function getCdekOrderStateName($id)
    {
        $states = self::getCdekOrderMainStates();
        if (array_key_exists($id, $states)) {
            return $states[$id];
        }

        return '';
    }

    /**
     * Возвращает список основных статусов заказа в учетной системе СДЭК
     *
     * @return array
     */
    public static function getCdekOrderMainStates()
    {
        return array(
            3  => 'Принят на склад отправителя',
            4  => 'Вручен',
            5  => 'Не вручен',
            6  => 'Выдан на отправку в городе-отправителе',
            7  => 'Сдан перевозчику в городе-отправителе',
            8  => 'Отправлен в город-получатель',
            9  => 'Встречен в город-получателе',
            10 => 'Принят на склад доставки',
            11 => 'Выдан на доставку',
            12 => 'Принят на склад до востребования',
            13 => 'Принят на склад транзита',
            16 => 'Возвращен на склад отправителя',
            17 => 'Возвращен на склад транзита',
            18 => 'Возвращен на склад доставки',
            19 => 'Выдан на отправку в городе-транзите',
            20 => 'Сдан перевозчику в городе-транзите',
            21 => 'Отправлен в город-транзит',
            22 => 'Встречен в город-транзите',

        );
    }

    /**
     * @param string $date YYYY-MM-DD Date of shipment
     * @param int $fromCityId Shipping city. Internal CDEK City code
     * @param int $toCityId City receiving the parcel. Internal CDEK City code
     * @param string|int $toCityZip Postal code of the city receiving the parcel. Russian 6-digit postal code
     * @param int $tariffId Tariff ID. Internal CDEK code
     * @param int $modeId Shipping mode ID
     * @param array $goods
     * @return mixed
     * @throws waException
     */
    public function calc($date, $fromCityId, $toCityId, $toCityZip, $tariffId, $modeId, $goods)
    {
        if (!$toCityId && !$toCityZip) {
            throw new waException('Для расчета доставки должен быть указан ID города и почтовый индекс. Или оба.');
        }

        $url = 'https://api.cdek.ru/calculator/calculate_price_by_json.php';

        $req_data = array(
            'version'      => '1.0',
            'dateExecute'  => $date,
            'authLogin'    => $this->authLogin,
            'secure'       => md5($date . '&' . $this->secretKey),
            'senderCityId' => $fromCityId,
            'tariffId'     => $tariffId,
            'goods'        => $goods
        );
        if ($modeId) {
            $req_data['modeId'] = $modeId;
        }

        if ($toCityId) {
            unset($this->request_headers['Content-Type']);
            $this->options['format'] = waNet::FORMAT_JSON;

            $result = $this->query($url, $req_data + array('receiverCityId' => $toCityId), waNet::METHOD_POST);

            // Если есть ответ и это не ошибка или есть ошибочный ответ, а индекса нет
            if ((!empty($result) && !isset($result['error'])) || (isset($result['error']) && !$toCityZip)) {
                return $result;
            }
        }

        unset($this->request_headers['Content-Type']);
        $this->options['format'] = waNet::FORMAT_JSON;

        $result = $this->query($url, $req_data + array('receiverCityPostCode' => $toCityZip), waNet::METHOD_POST);

        if (!is_array($result)) {
            throw new waException('API калькулятора вернул неверный ответ (не массив)');
        }

        return $result;
    }

    /**
     * Возвращает массив $result с результатами
     *
     * $result.errors[] глобальные ошибки запроса Необязательный элемент
     * $result.errors[].code код ошибки по СДЭКу
     * $result.errors[].message описание ошибки
     *
     * $result.orders[] массив результатов по заказу
     * $result.orders[].id ID заказа Shop-Script
     * $result.orders[].id_str Строковое представление заказа (с префиксом, как задан в настройках SS)
     * $result.orders[].status ok или fail
     * $result.orders[].dispatch_number если статус ok, то номер для отслеживания заказа
     * $result.orders[].errors[] если статус fail то массив аналогичный глобальным ошибкам code/message
     * $result.order_messages[] сообщения по всем заказам
     *
     * @param shopSdekintPluginDeliveryRequest $delivery_request
     * @return array
     * @throws waException
     */
    public function deliveryRequest(shopSdekintPluginDeliveryRequest $delivery_request)
    {
        $delivery_request->setAuthLogin($this->authLogin);
        $delivery_request->setKey($this->secretKey);
        $url = $this->api_url . 'new_orders.php';

        $this->options['format'] = waNet::FORMAT_XML;
        $this->request_headers['Content-Type'] = 'application/x-www-form-urlencoded';

        /** @var SimpleXMLElement $xml_result */
        $xml_result = $this->query($url, array('xml_request' => $delivery_request->asXML()), waNet::METHOD_POST);

        $result = array();

        $global_errors = $xml_result->xpath('//DeliveryRequest[@ErrorCode]');
        foreach ($global_errors as $error) {
            $result['errors'][] = array('code' => $error['ErrorCode'], 'message' => $error['Msg']);
        }

        $order_results = $xml_result->xpath('//Order');
        $result['orders'] = array();
        foreach ($order_results as $order_result) {
            $_r = array();
            if ($order_result['Number']) {
                foreach ($result['orders'] as $ok => $or) {
                    if ($or['id_str'] == $order_result['Number']) {
                        $_r = $or;
                        unset($result['orders'][$ok]);
                    }
                }
                $_r['id_str'] = (string)$order_result['Number'];
                $_r['id'] = shopHelper::decodeOrderId($order_result['Number']);
                if ($order_result['DispatchNumber']) {
                    $_r['status'] = 'ok';
                    $_r['dispatch_number'] = (string)$order_result['DispatchNumber'];
                }
                if ($order_result['ErrorCode']) {
                    $_r['status'] = 'fail';
                    $_r['errors'][] = array('code' => $order_result['ErrorCode'], 'message' => $order_result['Msg']);
                }
                $result['orders'][] = $_r;
            } elseif ($order_result['Msg']) {
                $result['order_messages'][] = (string)$order_result['Msg'];
            }
        }

        return $result;
    }

    /**
     * @param shopSdekintPluginDeleteRequest $delete_request
     * @throws waException
     */
    public function deleteRequest($delete_request)
    {
        $this->errors = array();

        $delete_request->setAuthLogin($this->authLogin);
        $delete_request->setKey($this->secretKey);
        $url = $this->api_url . 'delete_orders.php';

        $this->options['format'] = waNet::FORMAT_XML;
        $this->request_headers['Content-Type'] = 'application/x-www-form-urlencoded';

        /** @var SimpleXMLElement $xml_result */
        $xml_result = $this->query($url, array('xml_request' => $delete_request->asXML()), waNet::METHOD_POST);

        $err = array();

        foreach ($xml_result->xpath('//response[@ErrorCode]') as $trouble) {
            $err[] = (string)$trouble['Msg'];
        }
        foreach ($xml_result->xpath('//response/DeleteRequest[@ErrorCode]') as $trouble) {
            if ($trouble['Number']) {
                $this->errors[] = array(
                    'order_number' => (string)$trouble['Number'],
                    'message'      => (string)$trouble['Msg'],
                    'error_code'   => (string)$trouble['ErrorCode']
                );
            }
            $err[] = (string)$trouble['Msg'];
        }

        if (!empty($err)) {
            throw new waException(implode("\n", $err));
        }
    }

    /**
     * @param array $options
     * @return SimpleXMLElement
     * @throws waException
     */
    public function getOrderStates(array $options = array())
    {
        $url = $this->api_url . 'status_report_h.php';
        $this->options['format'] = waNet::FORMAT_XML;
        $this->options['timeout'] = 60;
        $defaults = ['order' => []];
        $this->request_headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $options += $defaults;

        /**
         * @var string|array $opt_order
         */
        extract($options, EXTR_PREFIX_ALL, 'opt');

        if (!is_array($opt_order)) {
            $opt_order = array($opt_order);
        }

        if (empty($opt_order)) {
            throw new waException('Список заказов для проверки пуст');
        }

        $date = new DateTime();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><StatusReport />');
        $xml['Date'] = $date->format('Y-m-d');
        $xml['Account'] = $this->authLogin;
        $xml['Secure'] = shopSdekintPluginHelper::getSecureKey($date, $this->secretKey, 'Y-m-d');
        $xml['ShowHistory'] = 0;
        $xml['ShowReturnOrder'] = 0;
        $xml['ShowReturnOrderHistory'] = 0;

        foreach ($opt_order as $order) {
            $order_node = $xml->addChild('Order');
            $order_node['DispatchNumber'] = $order['dispatch_number'];
        }

        return $this->query($url, array('xml_request' => $xml->asXML()), waNet::METHOD_POST);
    }

    /**
     * @param shopSdekintPluginOrdersPrintRequest $request
     * @return mixed
     * @throws waException
     */
    public function ordersPrintRequest($request)
    {
        $request->setAuthLogin($this->authLogin);
        $request->setKey($this->secretKey);
        $url = $this->api_url . 'orders_print.php';

        unset($this->request_headers['Content-Type']);
        $this->options['format'] = waNet::FORMAT_RAW;

        $answer = $this->query($url, array('xml_request' => $request->asXML()), waNet::METHOD_POST);

        if (empty($this->response_header['Content-Type'])) {
            throw new waException('Не удалось распознать тип ответа');
        }

        if ($this->response_header['Content-Type'] != 'application/pdf') {
            if (substr($this->response_header['Content-Type'], 0, 8) == 'text/xml') {
                $xml_err = new SimpleXMLElement($answer);
                $msgs = array();
                foreach ($xml_err->xpath('//response/Order') as $err) {
                    $msgs[] = $err['Msg'];
                }
                throw new waException(implode("\n", $msgs));
            }
            throw new waException('Не удалось распознать тип ответа: ' . $this->response_header['Content-Type']);
        }

        return $answer;
    }

    /**
     * Requests the list of pickup point by CityID or postal code
     * @return SimpleXMLElement
     * @throws waException
     */
    public function getPickupPoints()
    {
        $this->options['format'] = waNet::FORMAT_XML;
        $xml = $this->query($this->api_url . "pvzlist.php?type=ALL", null, waNet::METHOD_GET);

        return $xml;
    }

    public function CourierCallRequest($data)
    {
        $url = $this->api_url . 'call_courier.php';
        $default = array('call' => array());
        $data = array_merge($default, $data);
        $default_call = array(
            'Date'         => null,
            'TimeBeg'      => null, 'TimeEnd' => null, 'SendPhone' => null, 'SenderName' => null, 'Weight' => 0,
            'SendCityCode' => null, 'Comment' => ''
        );
        $default_address = array('Street' => null, 'House' => null, 'Flat' => null);

        foreach ($data['call'] as &$call) {
            if (is_array($call)) {
                $call = array_merge($default_call, $call);
            } else {
                $call = $default_call;
            }
            if (isset($call['Address']) && is_array($call['Address'])) {
                $call['Address'] = array_merge($default_address, $call['Address']);
            } else {
                $call['Address'] = $default_address;
            }
        }
        unset($call);

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><CallCourier />');

        $date = new DateTime();
        $xml->addAttribute('Date', $date->format('c'));
        $xml->addAttribute('Account', $this->authLogin);
        $xml->addAttribute('Secure', shopSdekintPluginHelper::getSecureKey($date, $this->secretKey));
        $xml->addAttribute('CallCount', count($data['call']));

        foreach ($data['call'] as $call) {
            $xml_call = $xml->addChild('Call');
            $xml_call->addAttribute('Date', $call['Date']);
            $xml_call->addAttribute('TimeBeg', $call['TimeBeg']);
            $xml_call->addAttribute('TimeEnd', $call['TimeEnd']);
            $xml_call->addAttribute('SendCityCode', $call['SendCityCode']);
            $xml_call->addAttribute('SendPhone', $call['SendPhone']);
            $xml_call->addAttribute('SenderName', $call['SenderName']);
            $xml_call->addAttribute('Weight', $call['Weight']);
            $xml_call->addAttribute('Comment', $call['Comment']);
            $xml_address = $xml_call->addChild('Address');
            $xml_address->addAttribute('Street', $call['Address']['Street']);
            $xml_address->addAttribute('House', $call['Address']['House']);
            $xml_address->addAttribute('Flat', $call['Address']['Flat']);
        }

//        $result = $this->request($url, array('xml_request' => $xml->asXML()));
        $this->options['format'] = waNet::FORMAT_XML;
        $this->request_headers['Content-Type'] = 'application/x-www-form-urlencoded';

        /** @var SimpleXMLElement $result */
        $result = $this->query($url, array('xml_request' => $xml->asXML()), waNet::METHOD_POST);
        return $result;
    }

    /**
     * @param array $content
     * @return string
     */
    protected function encodeRequest($content)
    {
        if (!is_string($content)) {
            switch ($this->options['format']) {
                case waNet::FORMAT_JSON:
                    $content = json_encode($content);
                    break;
                case waNet::FORMAT_XML:
                    if (is_object($content)) {
                        $class = get_class($content);
                        if ($class == 'SimpleXMLElement') {
                            /**
                             * @var SimpleXMLElement $content
                             */
                            $content = (string)$content->asXML();
                        } elseif ($class == 'DOMDocument') {
                            /**
                             * @var DOMDocument $content
                             */
                            if (!empty($this->options['charset'])) {
                                $content->encoding = $this->options['charset'];
                            }
                            $content->preserveWhiteSpace = false;
                            $content = (string)$content->saveXML();
                        }
                    } elseif (is_array($content)) {
                        $content = http_build_query($content);
                    }
                    break;
                default:
                    $content = http_build_query($content);
                    break;
            }
        }

        $this->request_headers['Content-Length'] = strlen($content);
        switch ($this->options['format']) {
            case waNet::FORMAT_JSON:
                $this->request_headers['Content-Type'] = 'application/json';
                break;

            case waNet::FORMAT_XML:
                if (empty($this->request_headers['Content-Type'])) {
                    $this->request_headers['Content-Type'] = 'application/xml';
                }
                break;
            case waNet::FORMAT_CUSTOM:
                //$this->request_headers['Content-Type'] ='application/'.$this->options['custom_content_type'];
                break;
            default:
                $this->request_headers['Content-Type'] = 'application/x-www-form-urlencoded';
                break;
        }
        if (!empty($this->options['md5'])) {
            $this->request_headers['Content-MD5'] = base64_encode(md5($content, true));
        }
        return $content;
    }

    protected function buildHeaders($transport, $raw = true)
    {
        $this->request_headers['Connection'] = 'close';
        $this->request_headers['Date'] = date('c');
        switch ($this->options['format']) {
            case self::FORMAT_JSON:
                $this->request_headers["Accept"] = "application/json";
                break;

            case self::FORMAT_XML:
                $this->request_headers["Accept"] = "text/xml,application/xml";
                break;

            default:
                $this->request_headers['Accept'] = '*/*';
                break;
        }

        $this->request_headers['Accept-Charset'] = $this->options['charset'];

        /**
         * Accept
         * | Accept-Charset           ; Section 14.2
         * | Accept-Encoding          ; Section 14.3
         * | Accept-Language          ; Section 14.4
         * | Authorization            ; Section 14.8
         * | Expect                   ; Section 14.20
         * | From                     ; Section 14.22
         * | Host                     ; Section 14.23
         * | If-Match                 ; Section 14.24
         */

        if (!empty($this->options['authorization'])) {
            $authorization = sprintf("%s:%s", $this->options['login'], $this->options['password']);
            $this->request_headers["Authorization"] = "Basic " . urlencode(base64_encode($authorization));
        }

        $this->request_headers['User-Agent'] = $this->user_agent;
        $this->request_headers['X-Framework-Method'] = $transport;

        if ($raw) {
            return $this->request_headers;
        } else {
            $headers = array();
            foreach ($this->request_headers as $header => $value) {
                $headers[] = sprintf('%s: %s', $header, $value);
            }
            return $headers;
        }
    }

}
