<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license Webasyst
 */

use SergeR\Webasyst\CdekSDK\API\Order\Request\OrdersPackagesPrint;

/**
 * Class shopSdekintPluginPrintBarcodeController
 */
class shopSdekintPluginPrintBarcodeController extends waViewController
{
    /**
     * @throws waException
     * @throws Exception
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

        try {
            if (empty($dispatch_ids)) {
                throw new waException('Ни в одном из перечисленных в запросе заказов не  найдено информации о номере отправления СДЭК');
            }
            $request = (new OrdersPackagesPrint)
                ->setCopyCount((int)$plugin->barcode_copy_count)
                ->setPrintFormat($plugin->barcode_format);

            array_walk($dispatch_ids, function ($dn) use ($request) {
                $request->addOrder($dn);
            });

            $result = $Api->ordersPackagesPrint($request);

            if ($result->isError()) {
                throw new waException('Ошибка СДЭК: ' . $result->getErrorMessage() . ' (' . $result->getErrorCode() . ')');
            }

            $this->getResponse()->addHeader('Content-type', 'application/pdf');
            $this->blocks = array($result->getData());
        } catch (Exception $e) {
            $view = wa()->getView();
            $view->assign('error', $e->getMessage());
            $this->blocks[] = $view->fetch(wa()->getAppPath('plugins/sdekint/templates/', 'shop') . 'printform_error.html');
        }
    }
}