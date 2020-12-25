<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Request;

use SergeR\Webasyst\CdekSDK\Type\PrintFormat;

/**
 * Class OrdersPackagesPrint
 * @package SergeR\Webasyst\CdekSDK\API\Order\Request
 */
class OrdersPackagesPrint extends AbstractPrintRequest
{
    /** @var string */
    protected $PrintFormat = PrintFormat::A4;

    /**
     * @return string
     */
    public function getPrintFormat()
    {
        return $this->PrintFormat;
    }

    /**
     * @param string $PrintFormat
     * @return OrdersPackagesPrint
     */
    public function setPrintFormat($PrintFormat)
    {
        $PrintFormat = strtoupper($PrintFormat);
        if (!in_array($PrintFormat, [PrintFormat::A4, PrintFormat::A5, PrintFormat::A6])) {
            throw new \InvalidArgumentException('Allowed print formats: A4, A5 and A6');
        }
        $this->PrintFormat = $PrintFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return '/ordersPackagesPrint';
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $arr = array(
            '@Date'        => $this->getFormattedDate(),
            '@Account'     => $this->getAccount(),
            '@Secure'      => $this->getSecure(),
            '@OrderCount'  => $this->getOrder()->getIterator()->count(),
            '@PrintFormat' => $this->PrintFormat,
            'Order'        => $this->getOrder()->toXmlArray(['tag' => null])
        );

        return ['OrdersPackagesPrint' => $arr];
    }
}