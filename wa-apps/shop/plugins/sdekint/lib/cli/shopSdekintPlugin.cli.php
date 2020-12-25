<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.3.0
 * @copyright Serge Rodovnichenko, 2016
 */
class shopSdekintPluginCli extends waCliController
{
    /** @var shopSdekintPlugin */
    private $plugin = null;

    /** @var waAppSettingsModel */
    private $AppSetting;

    public function execute()
    {
        shopSdekintPluginHelper::log('Выполнение заданий по расписанию cron');

//        if(function_exists('set_error_handler')) {
//            set_error_handler(function ($errNumber, $errStr, $errFile, $errLine){
//                throw new \ErrorException($errStr, 0, $errNumber, $errFile, $errLine);
//            });
//        }

        try {
            $this->checkStocks();
        } catch (waException $e) {
            shopSdekintPluginHelper::log('[CLI] Не удалось обновить списки ПВЗ. ' . $e->getMessage(), true);
        }

        try {
            $this->checkStates();
        } catch (waException $e) {
            shopSdekintPluginHelper::log('[CLI] Не удалось проверить состояние заказов. ' . $e->getMessage(), true);
        }
    }

    /**
     * Проверяет свежесть кэша ПВЗ и обновлет его каждые 12 часов
     */
    private function checkStocks()
    {
        shopSdekintPluginHelper::log('Проверка списка ПВЗ');
        $last_updated = $this->AppSetting->get('shop.sdekint', 'stockupdate', '0');
        $now = time();

        if ($now - $last_updated > 21600) {
            shopSdekintPluginHelper::log('Список ПВЗ устаревает, нужно обновить');
            try {
                $loaded = (new shopSdekintPluginPvzModel)->loadFromResponse(
                    $this->plugin->getOrdersApiClient()
                        ->pvzList(\SergeR\Webasyst\CdekSDK\API\Order\Request\PvzList::fromArray(['type' => 'ALL']))
                );
                $this->AppSetting->set('shop.sdekint', 'stockupdate', time());
                shopSdekintPluginHelper::log("Список ПВЗ обновлен из задания по расписанию. Сохранено $loaded строк");
            } catch (Exception $e) {
                shopSdekintPluginHelper::log($e->getMessage(), true);
            }
        }
    }

    /**
     * Проверка и смена статусов заказов, если включено в настройках
     */
    private function checkStates()
    {
        $check_interval = $this->plugin->auto_change_states;
        if (!$check_interval) {
            return;
        }

        $last_check = intval($this->AppSetting->get('shop.sdekint', 'statecheck', 0));

        if (time() - $last_check > $check_interval) {
            $OrderAction = new shopSdekintPluginOrderActionsModel();

            foreach ($OrderAction->order('id')->query() as $rule) {
                $rule = shopSdekintPluginHelper::typecastScalarArrayValues($rule, ['sdek_state' => 'int']);
                try {
                    $this->processOrders($rule);
                } catch (waException $e) {
                    shopSdekintPluginHelper::log(
                        sprintf("Ошибка при автоматической проверке статусов заказов: %s", $e->getMessage())
                    );
                }
            }
            $this->AppSetting->set('shop.sdekint', 'statecheck', time());
        }
    }

    /**
     * @param array $rule
     * @throws waException
     * @internal param $action
     */
    private function processOrders($rule)
    {
        $workflow = new shopWorkflow();
        $workflow_state = $workflow->getStateById($rule['shop_state']);
        $std_classes = ['shopWorkflowAction', 'shopWorkflowCompleteAction', 'shopWorkflowProcessAction', 'shopWorkflowDeleteAction', 'shopWorkflowShipAction'];

        if ($workflow_state->getId() != $rule['shop_state']) {
            throw new waException(sprintf('Unknown order state: %s', $rule['shop_state']));
        }
        $available_actions = $workflow_state->getActions();
        if (!array_key_exists($rule['wf_action'], $available_actions)) {
            throw new waException(sprintf('Action %s is not allowed for state %s', $rule['wf_action'], $rule['shop_state']));
        }

        $workflow_action = $workflow->getActionById($rule['wf_action']);

        if (!in_array(get_class($workflow_action), $std_classes)) {
            throw new waException('[CLI:processOrders] Невозможно выполнить действие класса "%s" над заказами', get_class($workflow_action));
        }

        $collection = new shopOrdersCollection('search/state_id=' . $workflow_state->getId() . '&params.sdekint_plugin.dispatch_number!=NULL');
        $offset = 0;
        $limit = 20;

        shopSdekintPluginHelper::log(sprintf(
            'Исполняем правило обработки заказов #%d: Для заказов, у которых в магазине статус "%s", а у СДЭКа статус "%s" исполняется действие "%s"',
            $rule['id'],
            $workflow_state->getName(),
            shopSdekintPluginHelper::findMainCdekStateName($rule['sdek_state']),
            $workflow_action->getName()
        ));

        while ($offset < $collection->count()) {
            $orders = $collection->getOrders('*,params', $offset, $limit);

            /** @var string[] $dispatch_numbers */
            $dispatch_numbers = array_filter(array_map(function ($i) {
                return ifempty($i, 'params', 'sdekint_plugin.dispatch_number', null);
            }, $orders));

            /** @var \SergeR\Webasyst\CdekSDK\Type\CdekOrder[] $dispatch_orders */
            $dispatch_orders = array_map(function ($dn) {
                return \SergeR\Webasyst\CdekSDK\Type\CdekOrder::fromArray(['DispatchNumber' => $dn]);
            }, $dispatch_numbers);

            try {

                $req = new \SergeR\Webasyst\CdekSDK\API\Order\Request\StatusReport();

                /** @var \SergeR\Webasyst\CdekSDK\Type\CdekOrder $dispatch_order */
                foreach ($dispatch_orders as $dispatch_order) {
                    $req->addOrder($dispatch_order);
                }

                $reports = $this->plugin->getOrdersApiClient()->statusReport($req);

                foreach ($orders as $o) {
                    // В параметрах заказа нет нужного параметра. Как он вообще сюда попал?
                    if (!($dn = ifempty($o, 'params', 'sdekint_plugin.dispatch_number', null))) {
                        continue;
                    }

                    // Среди треков нету трека нужного заказа. Какого хереса?!
                    if (!($report = $reports->getOrders()->findByDispatchNumber($dn))) {
                        continue;
                    }

                    // Статус в СДЭКе не совпадает с тем, что указан в правиле. Расходимся
                    if ($report->getStatus()->getCode() !== (int)$rule['sdek_state']) {
                        continue;
                    }

                    $workflow_action->run($o['id']);
                }
            }
            catch (Throwable $e) {
                shopSdekintPluginHelper::log(sprintf('[CLI:processOrders] Ошибка при обращении к API СДЭК: %s. [%s]', $e->getMessage(), implode(', ', $dispatch_numbers)), true);
            }
            catch (Exception $e) {
                shopSdekintPluginHelper::log(sprintf('[CLI:processOrders] Ошибка при обращении к API СДЭК: %s. [%s]', $e->getMessage(), implode(', ', $dispatch_numbers)), true);
            }

            $offset += $limit;
        }
    }


    /**
     * @throws waException
     */
    protected function preExecute()
    {
        try {
            shopSdekintPluginHelper::checkRequiredExtensions();
        } catch (waException $e) {
            shopSdekintPluginHelper::log($e->getMessage(), true);
            throw $e;
        }

        try {
            $this->plugin = wa('shop')->getPlugin('sdekint');
            $this->AppSetting = new waAppSettingsModel();
        } catch (waException $e) {
            shopSdekintPluginHelper::log($e->getMessage(), true);
            throw $e;
        }
    }
}
