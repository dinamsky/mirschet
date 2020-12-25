<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Client;

use Exception;
use LibXMLError;
use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\API\Order\Response\DeliveryRequestResponse;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\OrderAPIRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\SerializableParams;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\XmlSerializable;
use SergeR\Webasyst\CdekSDK\API\Order\Request\AbstractPrintRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Request\DeleteRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Request\DeliveryRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Request\OrdersPackagesPrint;
use SergeR\Webasyst\CdekSDK\API\Order\Request\OrdersPrint;
use SergeR\Webasyst\CdekSDK\API\Order\Request\PvzList;
use SergeR\Webasyst\CdekSDK\API\Order\Request\StatusReport;
use SergeR\Webasyst\CdekSDK\API\Order\Response\DeleteRequestResponse;
use SergeR\Webasyst\CdekSDK\API\Order\Response\PrintformResponse;
use SergeR\Webasyst\CdekSDK\API\Order\Response\PvzListResponse;
use SergeR\Webasyst\CdekSDK\API\Order\Response\StatusReportResponse;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;
use waException;
use waNet;

class WebasystOrderClient extends WebasystAbstractClient
{
    const API_ENTRYPOINT = 'https://integration.cdek.ru';

    /**
     * @param OrderAPIRequest $request
     * @param string $method
     * @param array $wanet_options
     * @param array $wanet_headers
     * @return array|SimpleXMLElement|string|waNet
     * @throws waException
     */
    public function query(OrderAPIRequest $request, $method = waNet::METHOD_GET, array $wanet_options = [], array $wanet_headers = [])
    {
        $url = static::API_ENTRYPOINT . $request->getEndpoint();

        if ($request instanceof AuthorizedRequest) {
            $request->setCredentials($this->account, $this->secure_password);
        }

        if (!Hash::get($wanet_options, 'request_format')) {
            $wanet_options['request_format'] = waNet::FORMAT_RAW;
        }

        $wanet_options = array_merge($this->getDefaultWanetOptions(), $wanet_options);
        $wanet_headers = array_merge($this->getDefaultWanetHeaders(), $wanet_headers);

        $net = new waNet($wanet_options, $wanet_headers);
        $content = array();
        if ($request instanceof XmlSerializable) {
            $serialized_request = $request->asXMLElement()->asXML();
            $this->_log($this->getLogLevel(), "Request {class}:\n{serialized}", ['class' => get_class($request), 'serialized' => $serialized_request]);
            $content = ['xml_request' => $serialized_request];
        }
        if ($request instanceof SerializableParams) {
            $content += $request->getFormParams();
            $this->_log($this->getLogLevel(), "Request {class}:\n{serialized}", ['class' => get_class($request), 'serialized' => var_export($content, true)]);
        }

        try {
            $net->query($url, $content, $method);
        } catch (waException $e) {
            $this->_logException($e);
            $type = $this->getHeader('Content-Type', (array)$net->getResponseHeader());
            if (stristr($type, 'application/xml') !== false) {
                return $net;
            }
            throw $e;
        }

        if ($this->getLogLevel()) {
            $headers = $net->getResponseHeader();
            $this->_log($this->getLogLevel(), "Response headers:\n{headers}", ['headers' => var_export($headers, true)]);
        }

        return $net;
    }

    /**
     * @param DeliveryRequest $request
     * @param array $options
     * @return DeliveryRequestResponse
     * @throws XmlException
     * @throws waException
     */
    public function deliveryRequest(DeliveryRequest $request, array $options = [])
    {
        $wanet_options = array_merge(['request_format' => waNet::FORMAT_RAW, 'format' => waNet::FORMAT_XML], (array)Hash::get($options, 'wanet.options'));
        $wanet_headers = array_merge(['Accept' => 'text/xml,application/xml'], (array)Hash::get($options, 'wanet.headers'));

        $response = $this->query($request, waNet::METHOD_POST, $wanet_options, $wanet_headers)->getResponse();

        if ($this->getLogLevel()) {
            $this->_log($this->getLogLevel(), "Response:\n{response}", ['response' => $response->asXML()]);
        }

        try {
            $result = DeliveryRequestResponse::fromXml($response);
        } catch (XmlException $e) {
            $this->_logException($e);
            throw $e;
        }

        return $result;
    }

    /**
     * @param PvzList $request
     * @param array $options
     * @return PvzListResponse
     * @throws waException
     */
    public function pvzList(PvzList $request, array $options = [])
    {
        $wanet_options = array_merge(['request_format' => waNet::FORMAT_RAW, 'format' => waNet::FORMAT_XML], (array)Hash::get($options, 'wanet.options'));
        $wanet_headers = array_merge(['Accept' => 'text/xml,application/xml'], (array)Hash::get($options, 'wanet.headers'));

        $response = $this->query($request, waNet::METHOD_GET, $wanet_options, $wanet_headers)->getResponse();

        return new PvzListResponse($response);
    }

    /**
     * @param OrdersPackagesPrint $request
     * @param array $options
     * @return PrintformResponse
     * @throws XmlException
     * @throws waException
     */
    public function ordersPackagesPrint(OrdersPackagesPrint $request, array $options = [])
    {
        return $this->printformRequest($request, $options);
    }

    /**
     * @param OrdersPrint $request
     * @param array $options
     * @return PrintformResponse
     * @throws XmlException
     * @throws waException
     */
    public function ordersPrint(OrdersPrint $request, array $options = [])
    {
        return $this->printformRequest($request, $options);
    }

    /**
     * @param AbstractPrintRequest $request
     * @param array $options
     * @return PrintformResponse
     * @throws XmlException
     * @throws waException
     */
    public function printformRequest(AbstractPrintRequest $request, array $options = [])
    {
        $wanet_options = array_merge(['request_format' => waNet::FORMAT_RAW, 'format' => waNet::FORMAT_RAW], (array)Hash::get($options, 'wanet.options'));
        $wanet_headers = array_merge(['Accept' => '*/*'], (array)Hash::get($options, 'wanet.headers'));

        $response = $this->query($request, waNet::METHOD_POST, $wanet_options, $wanet_headers);

        $result = new PrintformResponse();
        $content_type = $this->getHeader('Content-Type', (array)$response->getResponseHeader());

        if (($response->getResponseHeader('http_code') === 200) && (stristr($content_type, 'application/pdf') !== false)) {
            $result->setData($response->getResponse(true));
            return $result;
        }
        if (stristr($content_type, 'application/xml') !== false) {
            $err_xml = $this->_decodeXml($response->getResponse());
            $err = Xml::toArray($err_xml);
            $code = Hash::get($err, 'response.Order.@ErrorCode');
            $msg = Hash::get($err, 'response.Order.@Msg');
            if ($code) {
                return $result->setErrorCode($code)->setErrorMessage($msg);
            }
            throw new waException($response->getResponse(true), $response->getResponseHeader('http_code'));
        } else {
            throw new waException($response->getResponse(true), $response->getResponseHeader('http_code'));
        }
    }

    /**
     * @param StatusReport $request
     * @param array $options
     * @return StatusReportResponse
     * @throws waException
     * @throws Exception
     */
    public function statusReport(StatusReport $request, array $options = [])
    {
        $wanet_options = array_merge(['request_format' => waNet::FORMAT_RAW, 'format' => waNet::FORMAT_XML], (array)Hash::get($options, 'wanet.options'));
        $wanet_headers = array_merge(['Accept' => 'text/xml,application/xml'], (array)Hash::get($options, 'wanet.headers'));
        $response = $this->query($request, waNet::METHOD_POST, $wanet_options, $wanet_headers)->getResponse();

        if ($this->getLogLevel()) {
            $this->_log($this->getLogLevel(), "Response:\n{response}", ['response' => $response->asXML()]);
        }

        return StatusReportResponse::fromXML($response);
    }

    /**
     * @param DeleteRequest $request
     * @param array $options
     * @return DeleteRequestResponse
     * @throws XmlException
     * @throws waException
     */
    public function deleteRequest(DeleteRequest $request, array $options = [])
    {
        $wanet_options = array_merge(['request_format' => waNet::FORMAT_RAW, 'format' => waNet::FORMAT_XML], (array)Hash::get($options, 'wanet.options'));
        $wanet_headers = array_merge(['Accept' => 'text/xml,application/xml'], (array)Hash::get($options, 'wanet.headers'));
        $response = $this->query($request, waNet::METHOD_POST, $wanet_options, $wanet_headers)->getResponse();

        if ($this->getLogLevel()) {
            $this->_log($this->getLogLevel(), "Response:\n{response}", ['response' => $response->asXML()]);
        }

        return DeleteRequestResponse::fromXml($response);
    }

    /**
     * @param $xml
     * @return SimpleXMLElement
     * @throws waException
     */
    private function _decodeXml($xml)
    {
        $xml_options = LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET;
        libxml_use_internal_errors(true);
        libxml_disable_entity_loader(false);
        libxml_clear_errors();
        $xml = @simplexml_load_string($xml, null, $xml_options);

        if ($xml === false) {
            if ($error = libxml_get_last_error()) {
                /**
                 * @var LibXMLError $error
                 */
                $this->_log($this->getLogLevel(), 'Error while decode XML response: {msg}', ['msg' => $error->message]);
                throw new waException('Error while decode XML response: ' . $error->message, $error->code);
            }
        }

        return $xml;
    }

    /**
     * Регистронезависимая выборка заголовка из заголовков, возвращаемых waNet.
     * Обработка бага Webasyst Framework
     *
     * @param string $name
     * @param array $request_headers
     * @return mixed
     */
    private function getHeader($name, array $request_headers = [])
    {
        foreach ($request_headers as $field => $value) {
            if (strcasecmp($name, $field) === 0) {
                return $value;
            }
        }

        return null;
    }
}