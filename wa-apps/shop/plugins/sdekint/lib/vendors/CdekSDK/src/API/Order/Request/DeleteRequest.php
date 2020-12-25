<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Request;

use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequest;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequestTrait;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\OrderAPIRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\XmlSerializable;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;

/**
 * Class DeleteRequest
 * @package SergeR\Webasyst\CdekSDK\API\Order\Request
 */
class DeleteRequest implements XmlFriendlyArray, AuthorizedRequest, OrderAPIRequest, XmlSerializable
{
    use AuthorizedRequestTrait;

    /** @var string */
    protected $Number = '1';

    /** @var string[] */
    protected $Order = [];

    /**
     * DeleteRequest constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setDate(new \DateTimeImmutable());
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * @param string $Number
     * @return DeleteRequest
     */
    public function setNumber($Number)
    {
        $this->Number = $Number;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getOrders()
    {
        return $this->Order;
    }

    /**
     * @param string[] $Order
     * @return DeleteRequest
     */
    public function setOrders(array $Order)
    {
        $this->Order = array_filter(array_map(function ($o) {
            return (string)$o;
        }, $Order));

        return $this;
    }

    /**
     * @param string $order
     * @return DeleteRequest
     */
    public function addOrder($order)
    {
        $this->Order[] = $order;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderCount()
    {
        return count($this->Order);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return '/delete_orders.php';
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->getOrderCount() > 0;
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $tag = Hash::get($options, 'tag', 'DeleteRequest');
        $arr = array(
            '@Number'     => $this->getNumber(),
            '@Date'       => $this->getFormattedDate(),
            '@Account'    => $this->getAccount(),
            '@Secure'     => $this->getSecure(),
            '@OrderCount' => $this->getOrderCount(),
            'Order'       => array_map(function ($o) {
                return ['@Number' => $o];
            }, $this->getOrders())
        );

        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }

    /**
     * @return SimpleXMLElement
     * @throws XmlException
     */
    public function asXMLElement()
    {
        return Xml::fromArray($this->toXmlArray(), ['return' => 'simplexml']);
    }
}