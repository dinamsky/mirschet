<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2016-2019
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

use SergeR\Webasyst\CdekSDK\API\Order\Request\DeleteRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\CommonOrderResult;

/**
 * Отзыв/удаление заказа из СДЭК
 */
class shopSdekintPluginSdekDismissAction extends shopWorkflowAction
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /** @var array */
    public $errors = array();

    /**
     * @throws waException
     */
    protected function init()
    {
        parent::init();
        $this->plugin = wa('shop')->getPlugin('sdekint');
    }

    /**
     * Does all the actual work this action needs to do.
     * (declared as public for historical reasons only)
     * @param mixed $order_id implementation-specific parameter passed to $this->run(). see extract() call in the
     *     method body
     * @return mixed null if this action failed; any data to pass to $this->postExecute() if completed successfully.
     */
    public function execute($order_id = null)
    {
        if (is_null($order_id)) {
            return null;
        }

        $dispatch_number = $this->order_params_model->getOne($order_id, 'sdekint_plugin.dispatch_number');
        if (!$dispatch_number) {
            shopSdekintPluginHelper::log(sprintf('[shopSdekintPluginSdekDismissAction::execute]: Попытка удалить накладную из заказа без номера накладной %s', $order_id));
            return null;
        }

        $order_id_str = shopHelper::encodeOrderId($order_id);

        try {

            $result = $this->plugin->getOrdersApiClient()->deleteRequest((new DeleteRequest)->addOrder($order_id_str));
//            $result = $this->plugin->getOrdersApi()->deleteOrders([$order_id_str]);

            $deleted_order = $result->getOrders()->findByNumber($order_id_str);

            if (!($deleted_order instanceof CommonOrderResult)) {
                throw new waException('[shopSdekintPluginSdekDismissAction::execute]: В ответе сервера не найдена информация о заказе: ' . $order_id_str);
            }

            // Ошибка и это не "Заказ не найден"
            if ($deleted_order->isError() && ($deleted_order->getErrorCode() !== 'ERR_ORDER_NOTFIND')) {
                throw new waException($deleted_order->getErrorCode() . ': ' . $deleted_order->getErrorMessage());
            }
        } catch (Exception $e) {
            shopSdekintPluginHelper::log('[shopSdekintPluginSdekDismissAction::execute]: ' . $e->getMessage());
            return null;
        }

        if ($deleted_order->getErrorCode()) {
            shopSdekintPluginHelper::log(sprintf('Не найдена накладная на сервере СДЭК. Данные о накладной удалены из заказа %s', $order_id_str));
        }

        $order_params = (array)$this->order_params_model->get($order_id);
        if ($order_params) {
            $order_params = array_values(array_filter(array_keys($order_params), function ($n) {
                return mb_strpos($n, 'sdekint_plugin.', null, 'UTF-8') === 0;
            }));
        }

        if ($this->plugin->track_suggest) {
            $order_params[] = 'tracking_number';
        }

        if (empty($order_params)) {
            return array();
        }

        return array('update' => ['params' => array_fill_keys($order_params, null)]);
    }

    /**
     * Почти undocumented feature. В качестве параметра можно передать свои атрибуты для кнопки
     * видимо, wa спохватились, когда подтверждение удаления заказа делали :)
     *
     * @return string
     */
    public function getButton()
    {
        // data-container="#workflow-content"
        return parent::getButton('data-confirm="Заказ будет отозван из курьерской службы. Вы уверены?"');
    }

    /**
     * @param $order_id
     * @return null
     */
    public function getHTML($order_id)
    {
        return null;
    }

    /**
     * @param array $order
     * @return bool
     */
    public function isAvailable($order)
    {
        // С пустым заказом оно из настроек вызывается
        if (is_null($order)) {
            return true;
        }

        // Если нет номера накладной, то и отзывать нечего
        if (!ifset($order, 'params', 'sdekint_plugin.dispatch_number', false)) {
            return false;
        }

        return true;
    }

    /**
     * @param null|array $order_id Те же параметры, что и в execute
     * @param null $result
     * @return array|mixed
     */
    public function postExecute($order_id = null, $result = null)
    {
        $OrderParam = new shopOrderParamsModel();
        $OrderParam->exec(
            'DELETE FROM shop_order_params WHERE order_id=i:order_id AND NAME LIKE "sdekint_plugin.%"',
            array('order_id' => $order_id)
        );

        return parent::postExecute($order_id, $result);
    }

}
