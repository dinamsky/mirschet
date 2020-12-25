<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.0.0
 * @copyright Serge Rodovnichenko, 2015-2017
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\API\Order\Request\OrdersPrint;

/**
 * Class shopSdekintPluginPrintformController
 */
class shopSdekintPluginPrintformController extends waViewController
{
    /**
     * @throws XmlException
     * @throws waException
     */
    public function execute()
    {
        $order_ids = waRequest::get('order_id', null, waRequest::TYPE_ARRAY_INT);
        $numbers = waRequest::get('number', null, waRequest::TYPE_ARRAY_INT);
        $dispatch_ids = [];

        if (!$order_ids && !$numbers) {
            throw new waException('Нужен номер заказа');
        }

        /** @var shopSdekintPlugin $plugin */
        $plugin = wa('shop')->getPlugin('sdekint');
        $Api = $plugin->getOrdersApiClient();

        $dispatch_ids += (array)$numbers;

        if ($order_ids) {
            $OrderParam = new shopOrderParamsModel();
            foreach ($order_ids as $o) {
                $n = $OrderParam->getOne($o, 'sdekint_plugin.dispatch_number');
                if ($n) {
                    $dispatch_ids[] = $n;
                }
            };
        }

        $request = (new OrdersPrint)
            ->setCopyCount((int)$plugin->copy_count);

        array_walk($dispatch_ids, function ($dn) use($request){
            $request->addOrder((string)$dn);
        });

        try {
            $result = $Api->ordersPrint($request);
            if ($result->isError()) {
                throw new waException('Ошибка СДЭК: ' . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')');
            }

            $this->getResponse()->addHeader('Content-type', 'application/pdf');
            $this->blocks = array($result->getData());
        } catch (waException $e) {
            $view = wa()->getView();
            $view->assign('error', $e->getMessage());
            $this->blocks[] = $view->fetch(wa()->getAppPath('plugins/sdekint/templates/', 'shop') . 'printform_error.html');
        }
    }
}
