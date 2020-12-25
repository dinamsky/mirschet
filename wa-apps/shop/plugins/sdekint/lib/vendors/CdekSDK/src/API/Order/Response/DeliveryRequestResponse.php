<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response;

use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\CommonOrdersResultCollection;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\OrderResultCollection;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;

final class DeliveryRequestResponse
{
    /** @var OrderResultCollection */
    protected $Orders;

    /** @var CommonOrdersResultCollection */
    protected $Calls;

    /**
     * @param SimpleXMLElement $response
     * @return DeliveryRequestResponse
     * @throws XmlException
     */
    public static function fromXML(SimpleXMLElement $response)
    {
        $result = Xml::toArray($response);

        $orders = (array)Hash::get($result, 'response.Order');
        $calls = (array)Hash::get($result, 'response.Call');

        $obj = new self();
        $obj->Orders = OrderResultCollection::fromXmlArray(!empty($orders) && (Hash::maxDimensions($orders) < 2) ? [$orders] : $orders);
        $obj->Calls = CommonOrdersResultCollection::fromXmlArray(!empty($calls) && (Hash::maxDimensions($calls) < 2) ? [$calls] : $calls);

        return $obj;
    }

    /**
     * @return OrderResultCollection
     */
    public function getOrders()
    {
        return $this->Orders;
    }

    /**
     * @return CommonOrdersResultCollection
     */
    public function getCalls()
    {
        return $this->Calls;
    }
}