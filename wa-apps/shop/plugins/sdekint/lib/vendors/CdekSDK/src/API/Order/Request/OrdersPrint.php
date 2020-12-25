<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Request;

class OrdersPrint extends AbstractPrintRequest
{
    /**
     * СДЭК рекомендует минимум 2 копии
     *
     * @var int
     */
    protected $CopyCount = 2;

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return '/orders_print.php';
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        return array(
            'OrdersPrint' => array(
                '@Date'       => $this->getFormattedDate(),
                '@Account'    => $this->getAccount(),
                '@Secure'     => $this->getSecure(),
                '@OrderCount' => $this->getOrder()->getIterator()->count(),
                '@CopyCount'  => $this->getCopyCount(),
                'Order'       => $this->Order->toXmlArray(['tag' => null])
            )
        );
    }
}