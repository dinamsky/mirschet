<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Client;

use Exception;
use SergeR\Webasyst\CdekSDK\API\Calculator\Request\CalculationRequest;
use SergeR\Webasyst\CdekSDK\API\Calculator\Request\CalculationTariffListRequest;
use SergeR\Webasyst\CdekSDK\API\Calculator\Response\CalculationResponse;
use SergeR\Webasyst\CdekSDK\API\Calculator\Response\CalculationTariffListResponse;
use SergeR\Webasyst\CdekSDK\Type\Geoname;
use SergeR\Webasyst\CdekSDK\Type\GeonamesCollection;
use SergeR\CakeUtility\Hash;
use waException;
use waNet;
use waUtils;

/**
 * Class WebasystCalculatorClient
 * @package SergeR\Webasyst\CdekSDK\Client
 */
class WebasystCalculatorClient extends WebasystAbstractClient
{
    const API_ENTRYPOINT = 'https://api.cdek.ru';

    /**
     * @param CalculationRequest $request
     * @param array $options
     * @return CalculationResponse
     * @throws waException
     * @throws Exception
     */
    public function calc(CalculationRequest $request, array $options = [])
    {
        $wanet_options = array_merge(['request_format' => waNet::FORMAT_JSON, 'format' => waNet::FORMAT_JSON], (array)Hash::get($options, 'wanet.options'));
        $wanet_headers = (array)Hash::get($options, 'wanet.headers');

        $wanet_options = array_merge($this->getDefaultWanetOptions(), $wanet_options);
        $wanet_headers = array_merge($this->getDefaultWanetHeaders(), $wanet_headers);

        if ($this->account && $this->secure_password) {
            $request->setCredentials($this->account, $this->secure_password);
        }

        $url = self::API_ENTRYPOINT . '/calculator/calculate_price_by_json.php';
        $content = $request->toArray();
        $net = new waNet($wanet_options, $wanet_headers);

        if ($this->getLogLevel()) {
            $this->_log($this->getLogLevel(), "Request {class}:\n{serialized}", ['class' => get_class($request), 'serialized' => var_export($content, true)]);
        }

        try {
            $result = $net->query($url, $content, waNet::METHOD_POST);
        } catch (waException $e) {
            $this->_logException($e);
            throw $e;
        }

        if ($this->getLogLevel()) {
            $headers = $net->getResponseHeader();
            $this->_log($this->getLogLevel(), "Response headers:\n{headers}", ['headers' => var_export($headers, true)]);
            $this->_log($this->getLogLevel(), "Response:\n{response}", ['response' => var_export($result, true)]);
        }

        return CalculationResponse::fromArray($result);
    }

    public function calc_tarifflist(CalculationTariffListRequest $request, array $options = [])
    {
        $wanet_options = array_merge(['request_format' => waNet::FORMAT_JSON, 'format' => waNet::FORMAT_JSON], (array)Hash::get($options, 'wanet.options'));
        $wanet_headers = (array)Hash::get($options, 'wanet.headers');

        $wanet_options = array_merge($this->getDefaultWanetOptions(), $wanet_options);
        $wanet_headers = array_merge($this->getDefaultWanetHeaders(), $wanet_headers);

        if ($this->account && $this->secure_password) {
            $request->setCredentials($this->account, $this->secure_password);
        }

        $url = self::API_ENTRYPOINT . '/calculator/calculate_tarifflist.php';
        $net = new waNet($wanet_options, $wanet_headers);

        if ($this->getLogLevel()) {
            $this->_log($this->getLogLevel(), "Request {class}:\n{serialized}", ['class' => get_class($request), 'serialized' => waUtils::jsonEncode($request)]);
        }

        try {
            $result = $net->query($url, $request, waNet::METHOD_POST);
        } catch (Exception $e) {
            $this->_logException($e);
            throw $e;
        }

        if ($this->getLogLevel()) {
            $headers = $net->getResponseHeader();
            $this->_log($this->getLogLevel(), "Response headers:\n{headers}", ['headers' => var_export($headers, true)]);
            $this->_log($this->getLogLevel(), "Response:\n{response}", ['response' => var_export($result, true)]);
        }

        return new CalculationTariffListResponse($result);
    }

    /**
     * Автокомплит городоа по строке
     *
     * @param string $term
     * @param array $options
     * @return GeonamesCollection
     * @throws waException
     */
    public function cityGetListByTerm($term, array $options = [])
    {
        $wanet_options = array_merge(
            $this->getDefaultWanetOptions(),
            ['request_format' => waNet::FORMAT_JSON, 'format' => waNet::FORMAT_JSON],
            (array)Hash::get($options, 'wanet.options')
        );
        $wanet_headers = array_merge(
            (array)Hash::get($options, 'wanet.headers'),
            $this->getDefaultWanetHeaders()
        );

        $url = self::API_ENTRYPOINT . '/city/getListByTerm/json.php';

        $content = ['q' => $term];
        $net = new waNet($wanet_options, $wanet_headers);

        if ($this->getLogLevel()) {
            $this->_log($this->getLogLevel(), "Request getListByTerm:\n{serialized}", ['serialized' => var_export($content, true)]);
        }

        try {
            $response = $net->query($url, $content, waNet::METHOD_GET);
        } catch (waException $e) {
            $this->_logException($e);
            throw $e;
        }

        if ($this->getLogLevel()) {
            $headers = $net->getResponseHeader();
            $this->_log($this->getLogLevel(), "Response headers:\n{headers}", ['headers' => var_export($headers, true)]);
            $this->_log($this->getLogLevel(), "Response:\n{response}", ['response' => var_export($response, true)]);
        }

        $geonames_array = (array)Hash::get($response, 'geonames');
        $result = new GeonamesCollection();
        foreach ($geonames_array as $item) {
            $city = Geoname::fromArray($item);
            if ($city->getId()) {
                $result->add($city);
            }
        }

        return $result;
    }
}