<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2017
 * @license
 */
class shopSdekintPluginSdekCalcApi
{
    /** @var string */
    protected $auth;

    /** @var string */
    protected $key;

    /** @var waNet|shopSdekintPluginNet */
    protected $net;

    /** @var string */
    protected $api_host = 'http://api.cdek.ru/calculator/calculate_price_by_json.php';

    /**
     * @param string|null $auth
     * @param string|null $key
     * @param waNet|shopSdekintPluginNet|null $net
     */
    function __construct($auth = null, $key = null, waNet $net = null)
    {
        if ($auth) {
            $this->auth = $auth;
        }

        if ($key) {
            $this->key = $key;
        }

        if ($net) {
            $this->net = $net;
        } else {
            $this->net = new waNet(['format' => waNet::FORMAT_JSON]);
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws waException
     */
    public function calc(array $data)
    {
        if (!ifset($data, 'to_city_id', null) && !ifset($data, 'to_city_zip', null)) {
            throw new waException('[CALC] Для расчета доставки должен быть указан ID города и почтовый индекс. Или оба.');
        }

        $req_data = array(
            'version'      => '1.0',
            'dateExecute'  => ifempty($data, 'date', date('Y-m-d')),
            'authLogin'    => $this->auth,
            'secure'       => $this->_secure($data['date']),
            'senderCityId' => $data['from_city_id'],
            'tariffId'     => $data['tariff_id'],
            'goods'        => $data['goods']
        );

        if (ifset($data, 'mode_id', null)) {
            $req_data['modeId'] = $data['mode_id'];
        }

        if (ifset($data, 'to_city_id', null)) {
            $result = $this->net->query($this->api_host, $req_data + array('receiverCityId' => $data['to_city_id']), waNet::METHOD_POST);

            // Если есть ответ и это не ошибка или есть ошибочный ответ, а индекса нет
            if ((!empty($result) && !isset($result['error'])) || (isset($result['error']) && !ifset($data, 'to_city_zip', null))) {
                return $result;
            }
        }

        unset($req_data['receiverCityId']);
        $result = $this->net->query($this->api_host, $req_data + array('receiverCityPostCode' => $data['to_city_zip']), waNet::METHOD_POST);

        if (!is_array($result)) {
            throw new waException('API калькулятора вернул неверный ответ (не массив)');
        }

        return $result;

    }

    /**
     * @param $name
     * @return string
     * @throws waException
     */
    public function city($name)
    {
        $result = $this->net->query('https://api.cdek.ru/city/getListByTerm/json.php', ['q'=>$name], waNet::METHOD_GET);

        return $result;
    }

    /**
     * @param null|string $date
     * @return string
     */
    protected function _secure($date = null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }

        return md5($date . '&' . $this->key);
    }
}
