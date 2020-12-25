<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response;

use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\CommonOrdersResultCollection;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;

/**
 * Class DeleteRequestResponse
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response
 */
final class DeleteRequestResponse
{
    /**
     * @var string
     */
    protected $Msg = '';

    /**
     * @var CommonOrdersResultCollection
     */
    protected $Order;

    /**
     * DeleteRequestResponse constructor.
     */
    public function __construct()
    {
        $this->Order = new CommonOrdersResultCollection;
    }

    /**
     * @return string
     */
    public function getMsg()
    {
        return $this->Msg;
    }

    /**
     * @return CommonOrdersResultCollection
     */
    public function getOrders()
    {
        return $this->Order;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return DeleteRequestResponse
     * @throws XmlException
     */
    public static function fromXml(SimpleXMLElement $xml)
    {
        $self = new self();

        $data = Xml::toArray($xml);

        // С января 2019 вместо <Response><DeleteRequest><Order>... в ответе <response><Order>
        // Как на тестовом аккаунте неясно, поэтому двойная обработка
        $_data = (array)Hash::get($data, 'Response.DeleteRequest');
        if (empty($_data)) {
            $_data = (array)Hash::get($data, 'response');
        }

        $data = $_data;


        $self->Msg = (string)Hash::get($data, '@Msg');
        $orders = (array)Hash::get($data, 'Order');
        if (!Hash::numeric(array_keys($orders))) {
            $orders = array($orders);
        }
        $self->Order = CommonOrdersResultCollection::fromXmlArray($orders);

        return $self;
    }
}