<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Request;

use DateTimeImmutable;
use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequest;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequestTrait;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\OrderAPIRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\XmlSerializable;
use SergeR\Webasyst\CdekSDK\Type\CdekOrder;
use SergeR\Webasyst\CdekSDK\Type\CdekOrderCollection;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;

/**
 * Class StatusReport
 * @package SergeR\Webasyst\CdekSDK\API\Order\Request
 */
class StatusReport implements XmlFriendlyArray, AuthorizedRequest, OrderAPIRequest, XmlSerializable
{
    use AuthorizedRequestTrait {
        getFormattedDate as getFormattedRequestDate;
    }

    /** @var int */
    protected $ShowHistory = 0;

    /** @var int */
    protected $ShowReturnOrder = 0;

    /** @var int */
    protected $ShowReturnOrderHistory = 0;

    /** @var DateTimeImmutable|null */
    protected $DateFirst;

    /** @var DateTimeImmutable|null */
    protected $DateLast;

    /** @var CdekOrderCollection */
    protected $Order;

    /**
     * StatusReport constructor.
     */
    public function __construct()
    {
        $this->Order = new CdekOrderCollection;
        $this->Date = new DateTimeImmutable();
    }

    /**
     * @return int
     */
    public function getShowHistory()
    {
        return $this->ShowHistory;
    }

    /**
     * @param int $ShowHistory
     * @return StatusReport
     */
    public function setShowHistory($ShowHistory)
    {
        $this->ShowHistory = $ShowHistory;
        return $this;
    }

    /**
     * @return int
     */
    public function getShowReturnOrder()
    {
        return $this->ShowReturnOrder;
    }

    /**
     * @param int $ShowReturnOrder
     * @return StatusReport
     */
    public function setShowReturnOrder($ShowReturnOrder)
    {
        $this->ShowReturnOrder = $ShowReturnOrder;
        return $this;
    }

    /**
     * @return bool
     */
    public function getShowReturnOrderHistory()
    {
        return (bool)$this->ShowReturnOrderHistory;
    }

    /**
     * @param bool $ShowReturnOrderHistory
     * @return $this
     */
    public function setShowReturnOrderHistory($ShowReturnOrderHistory)
    {
        $this->ShowReturnOrderHistory = (int)$ShowReturnOrderHistory;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDateFirst()
    {
        return $this->DateFirst;
    }

    /**
     * @param DateTimeImmutable|null $DateFirst
     * @return StatusReport
     */
    public function setDateFirst($DateFirst)
    {
        $this->DateFirst = $DateFirst;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDateLast()
    {
        return $this->DateLast;
    }

    /**
     * @param DateTimeImmutable|null $DateLast
     * @return StatusReport
     */
    public function setDateLast($DateLast)
    {
        $this->DateLast = $DateLast;
        return $this;
    }

    /**
     * @return CdekOrderCollection
     */
    public function getOrders()
    {
        return $this->Order;
    }

    /**
     * @param CdekOrderCollection $Order
     * @return $this
     */
    public function setOrders($Order)
    {
        $this->Order = $Order;
        return $this;
    }

    /**
     * @param CdekOrder $order
     * @return $this
     */
    public function addOrder(CdekOrder $order)
    {
        $this->Order->add($order);
        return $this;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getFormattedDate($format = 'Y-m-d')
    {
        if (!$this->DateFirst || !$this->DateLast) {
            return $this->getFormattedRequestDate($format);
        }

        if (property_exists($this, '_date_format') && ($format === null)) {
            if (($_format = (string)$this->_date_format)) {
                $format = $_format;
            } else {
                $format = 'Y-m-d';
            }
        }

        return $this->getDateFirst()->format($format);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return '/status_report_h.php';
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $tag = Hash::get($options, 'tag', 'StatusReport');

        $arr = array(
            '@Date'                   => $this->getFormattedDate(),
            '@Account'                => $this->getAccount(),
            '@Secure'                 => $this->getSecure(),
            '@ShowHistory'            => (int)$this->getShowHistory(),
            '@ShowReturnOrder'        => (int)$this->getShowReturnOrder(),
            '@ShowReturnOrderHistory' => (int)$this->getShowReturnOrderHistory(),
            'Order'                   => $this->getOrders()->toXmlArray(['tag' => null])
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
        return Xml::build($this->toXmlArray(), ['return' => 'simplexml']);
    }
}