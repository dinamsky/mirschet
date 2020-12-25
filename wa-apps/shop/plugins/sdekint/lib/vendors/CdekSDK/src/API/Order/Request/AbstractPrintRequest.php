<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Request;


use DateTimeImmutable;
use Exception;
use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequest;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequestTrait;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\OrderAPIRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\XmlSerializable;
use SergeR\Webasyst\CdekSDK\Type\CdekOrder;
use SergeR\Webasyst\CdekSDK\Type\CdekOrderCollection;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;

abstract class AbstractPrintRequest implements OrderAPIRequest, AuthorizedRequest, XmlSerializable, XmlFriendlyArray
{
    use AuthorizedRequestTrait;

    /** @var int */
    protected $CopyCount = 1;

    /** @var CdekOrderCollection */
    protected $Order;

    public function __construct()
    {
        $this->Order = new CdekOrderCollection();
        try {
            $this->Date = new DateTimeImmutable();
        } catch (Exception $e) {
            //do nothing
        }
    }

    /**
     * @return int
     */
    public function getCopyCount()
    {
        return $this->CopyCount;
    }

    /**
     * @param int $CopyCount
     * @return $this
     */
    public function setCopyCount($CopyCount)
    {
        $this->CopyCount = $CopyCount;
        return $this;
    }
    /**
     * @return CdekOrderCollection
     */
    public function getOrder()
    {
        return $this->Order;
    }

    /**
     * @param CdekOrderCollection $Order
     * @return $this
     */
    public function setOrder($Order)
    {
        $this->Order = $Order;
        return $this;
    }

    /**
     * @param CdekOrder|string $order
     * @return $this
     */
    public function addOrder($order)
    {
        if (is_string($order)) {
            $order = (new CdekOrder)->setDispatchNumber($order);
        }

        $this->Order->add($order);

        return $this;
    }

    /**
     * @return SimpleXMLElement
     * @throws XmlException
     */
    public function asXMLElement()
    {
        return Xml::fromArray($this->toXmlArray());
    }
}