<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Request;

use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequest;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequestTrait;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\OrderAPIRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\XmlSerializable;
use SergeR\Webasyst\CdekSDK\Type\CourierCall;
use SergeR\Webasyst\CdekSDK\Type\CourierCallCollection;
use SergeR\Webasyst\CdekSDK\Type\ShopOrder;
use SergeR\Webasyst\CdekSDK\Type\ShopOrderCollection;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;

class DeliveryRequest implements XmlFriendlyArray, AuthorizedRequest, OrderAPIRequest, XmlSerializable
{
    use AuthorizedRequestTrait;

    /** @var string */
    protected $Number = '1';

    /** @var ShopOrderCollection */
    protected $Order;

    /** @var CourierCallCollection */
    protected $CallCourier;

    /**
     * DeliveryRequest constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->Date = new \DateTimeImmutable();
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
     * @return DeliveryRequest
     */
    public function setNumber($Number)
    {
        $this->Number = $Number;
        return $this;
    }

    /**
     * @param ShopOrder $order
     * @return $this
     */
    public function addOrder(ShopOrder $order)
    {
        if (!$this->Order) {
            $this->Order = new ShopOrderCollection();
        }
        $this->Order->add($order);

        return $this;
    }

    /**
     * @return ShopOrderCollection
     */
    public function getOrder()
    {
        return $this->Order;
    }

    /**
     * @param ShopOrderCollection $Order
     * @return DeliveryRequest
     */
    public function setOrder(ShopOrderCollection $Order)
    {
        $this->Order = clone $Order;
        return $this;
    }

    /**
     * @param CourierCall $call
     * @return $this
     */
    public function addCallCourier(CourierCall $call)
    {
        if (!$this->CallCourier) {
            $this->CallCourier = new CourierCallCollection();
        }
        $this->CallCourier->add($call);

        return $this;
    }

    /**
     * @return CourierCallCollection
     */
    public function getCallCourier()
    {
        return $this->CallCourier;
    }

    /**
     * @param CourierCallCollection $CallCourier
     * @return DeliveryRequest
     */
    public function setCallCourier(CourierCallCollection $CallCourier)
    {
        $this->CallCourier = clone $CallCourier;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderCount()
    {
        return $this->getOrder()->count();
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $arr = array(
            '@Number'     => $this->getNumber(),
            '@Date'       => $this->getFormattedDate(),
            '@Account'    => $this->getAccount(),
            '@Secure'     => $this->getSecure(),
            '@OrderCount' => $this->getOrder()->count()
        );

        if ($this->getOrder() && !$this->getOrder()->isEmpty()) {
            $arr['Order'] = $this->getOrder()->toXmlArray(['tag' => null]);
        }

        if ($this->getCallCourier() && !$this->getCallCourier()->isEmpty()) {
            $arr['CallCourier'] = $this->getCallCourier()->toXmlArray();
        }

        return ['DeliveryRequest' => $arr];
    }

    /**
     * @return SimpleXMLElement
     * @throws XmlException
     */
    public function asXMLElement()
    {
        /** @var SimpleXMLElement $xml */
        $xml = Xml::build($this->toXmlArray(), ['return' => 'simplexml']);

        return $xml;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return '/new_orders.php';
    }
}