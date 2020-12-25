<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2017
 * @license
 */
class shopSdekintPluginCurrencyRateController extends waJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $from = waRequest::get('from', 'RUB');
        $to = waRequest::get('to', 'RUB');

        /** @var shopSdekintPlugin $plugin */
        $plugin = wa('shop')->getPlugin('sdekint');

        if ($from === $to) {
            $this->response = ['rate' => 1.0];
            return;
        }

        /**
         * @var shopConfig $config
         */
        $config = wa('shop')->getConfig();
        $rate = 1.0;

        $primary = $config->getCurrency(true);
        $currencies = $config->getCurrencies(array($from, $to));
        if (isset($currencies[$from])) {
            if ($currencies[$from] != $primary) {
                $rate *= ifset($currencies, $from, 'rate', false) ? $plugin->helper->toFloat($currencies[$from]['rate']) : 1.0;
            }
        } else {
            $this->response['warnings'][] = sprintf('В настройках магазина не задана валюта %s. Конвертация произведена с ошибками', $from);
        }

        if ($to != $primary) {
            if (isset($currencies[$to])) {
                $rate /= ifset($currencies, $to, 'rate', false) ? $plugin->helper->toFloat($currencies[$to]['rate']) : 1.0;
            } else {
                $this->response['warnings'][] = sprintf('В настройках магазина не задана валюта %s. Конвертация произведена с ошибками', $to);
            }
        }
        $this->response['rate'] = $rate;
    }
}
