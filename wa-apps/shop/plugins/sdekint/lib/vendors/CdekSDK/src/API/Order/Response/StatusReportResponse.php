<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response;

use DateTimeImmutable;
use Exception;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\StatusReportOrderCollection;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;

/**
 * Class StatusReportResponse
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response
 */
final class StatusReportResponse
{
    /** @var DateTimeImmutable */
    protected $DateFirst;

    /** @var DateTimeImmutable */
    protected $DateLast;

    /** @var StatusReportOrderCollection */
    protected $Order;

    /**
     * StatusReportResponse constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->DateFirst = new DateTimeImmutable();
        $this->DateLast = new DateTimeImmutable();
        $this->Order = new StatusReportOrderCollection();
    }

    /**
     * @param SimpleXMLElement $response
     * @return StatusReportResponse
     * @throws Exception
     */
    public static function fromXML(SimpleXMLElement $response)
    {
        return self::fromArray((array)Hash::get(Xml::toArray($response), 'StatusReport'));
    }

    /**
     * @return StatusReportOrderCollection
     */
    public function getOrders()
    {
        return $this->Order;
    }

    /**
     * @param array $xml_array
     * @return StatusReportResponse
     * @throws Exception
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $df = Hash::get($xml_array, '@DateFirst');
        if ($df) {
            $self->DateFirst = new DateTimeImmutable($df);
        }
        if (($dl = Hash::get($xml_array, '@DateLast'))) {
            $self->DateLast = new DateTimeImmutable($dl);
        }
        $order = (array)Hash::get($xml_array, 'Order');
        if (!empty($order) && !Hash::numeric(array_keys($order))) {
            $order = array($order);
        }
        $self->Order = StatusReportOrderCollection::fromArray($order);

        return $self;
    }
}