<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopDelpayfilterPlugin extends shopPlugin
{

    private static $active_shipping = array();

    // Данный хук необходим для того, чтобы в корзине отлавливать POST/GET запросы. Иначе они не сохраняются
    public function orderCalculateDiscount($params)
    {
        static $inited = 0;
        // Учитываем плагин "Купить в 1 клик" (quickorder)
        if (!$inited && waRequest::param('plugin', '') !== 'quickorder' && wa()->getEnv() == 'frontend') {
            $inited = 1;
            self::getFailedMethods();
        }
    }

    public function frontendOrderCartVars(&$params)
    {
        $output = array();

        /* Осуществляем поиск условий "по данным пользователя".
           Если такое условие будет найдено, тогда отслеживаем на витрине изменение данных пользователя и при необходимости
           обновляем данные доставки и оплаты.
        */
        $filters = shopDelpayfilterHelper::getFilters();
        $target_types = array();
        foreach ($filters as $rule) {
            if ($rule['status']) {
                // Условия
                $conditions = shopDelpayfilterConditions::decode($rule['conditions']);
                if (!empty($conditions['conditions'])) {
                    if ($this->findUserConditions($conditions['conditions'])) {
                        $targets = shopDelpayfilterConditions::decode($rule['target']);
                        $target_types += $this->getTargetTypes($targets);
                    }
                }
            }
        }
        if ($target_types) {
            $output['bottom'] = '<script>';
            $output['bottom'] .= '$(document).on("change", "#wa-step-auth-section input, #wa-step-auth-section select, #wa-step-auth-section textarea", function() {';
            $output['bottom'] .= 'var controller = $("#js-order-form").data("controller");';
            $output['bottom'] .= 'if (controller !== undefined && (' . (isset($target_types['shipping']) ? 'controller.sections.shipping.$form.length' : '') . (isset($target_types['payment']) && isset($target_types['shipping']) ? ' || ' : '') . (isset($target_types['payment']) ? 'controller.sections.payment.$form.length' : '') . ')) {';
            $output['bottom'] .= '$(document).trigger("wa_order_cart_changed");';
            $output['bottom'] .= '}';
            $output['bottom'] .= '});';
            $output['bottom'] .= '</script>';
        }

        return $output;
    }

    /**
     * Check if conditions have "user_data"
     *
     * @param $conditions
     * @return int
     */
    private function findUserConditions($conditions)
    {
        $result = 0;
        foreach ($conditions as $c) {
            // Если перед нами группа скидок, разбираем ее
            if (isset($c['group_op'])) {
                $conditions2 = shopDelpayfilterConditions::decode($c['conditions']);
                $result = $this->findUserConditions($conditions2);
                if ($result) {
                    break;
                }
            } elseif ($c['type'] == 'user_data') {
                $result = 1;
                break;
            }
        }
        return $result;
    }

    /**
     * Get active target types of the
     *
     * @param $targets
     * @return array
     */
    private function getTargetTypes($targets)
    {
        $types = array();
        foreach ($targets as $target) {
            if ($target['target'] == 'payment') {
                $types['payment'] = 1;
            } elseif ($target['target'] == 'shipping') {
                $types['shipping'] = 1;
            }
        }
        return $types;
    }

    public function checkoutAfterShipping($params)
    {
        // Меняем отображение
        if (self::$active_shipping) {
            $params['process_result']['data']['input']['shipping']['type_id'] =
            $params['data']['input']['shipping']['type_id'] =
                self::$active_shipping['type'];
            $params['process_result']['data']['input']['shipping']['variant_id'] =
            $params['data']['input']['shipping']['variant_id'] =
                self::$active_shipping['variant_id'];
            $params['process_result']['data']['shipping']['selected_variant'] = self::$active_shipping['data'];
            $params['process_result']['result']['selected_type_id'] = self::$active_shipping['type'];
            $params['process_result']['result']['selected_variant_id'] = self::$active_shipping['variant_id'];
        } else if (!empty($params['process_result']['data']) && $params['process_result']['data']['origin'] == 'form') {

            // Сохраняем информацию о заказе
            $cache = new waRuntimeCache('delpayfilter_checkout_params');
            $checkout_params = array(
                'shipping' => $this->getShippingParams($params['process_result']['result']),
                'contact' => ''
            );
            $cache->set($checkout_params);

            // Собираем все методы в массив
            $methods = $this->parseMethods($params['process_result']['result']['types']);

            // Фильтруем методы
            $output_methods = self::filterDeliveryMethods($methods['ids'], true);

            // Удаляем методы на витрине
            $this->hideShippingMethods(
                $methods['methods'],
                $output_methods,
                $checkout_params,
                $params['process_result']['result'],
                $params['process_result']['data']['input']
            );
        }
        // Если в способе доставки указана ошибка, что не выбран вариант доставки, а у нас имеется выбранный на основе фильтров,
        // устанавливаем этот метод в качестве основного, удаляем ошибку.
        $errors = ifset($params['process_result'], 'errors', null);
        if ($errors) {
            $empty_variant_errors = array_filter($errors, function ($error) {
                return $error['name'] == 'shipping[variant_id]';
            });
            if (self::$active_shipping && count($empty_variant_errors) === count($errors)) {
                $params['process_result']['errors'] = null;
                $params['process_result']['can_continue'] = true;
                $address = [
                    'country' => ifset($params['data'], 'result', 'region', 'selected_values', 'country_id', null),
                    'region' => ifset($params['data'], 'result', 'region', 'selected_values', 'region_id',
                        ifset($params['data'], 'result', 'region', 'selected_values', 'region', null)
                    ),
                    'city' => ifset($params['data'], 'result', 'region', 'selected_values', 'city_id',
                        ifset($params['data'], 'result', 'region', 'selected_values', 'city', null)
                    ),
                    'zip' => ifset($params['data'], 'result', 'region', 'selected_values', 'zip', null),
                ];
                $route = wa()->getRouting()->getRoute();
                $checkout_config = new shopCheckoutConfig(ifset($route, 'checkout_storefront_id', null));
                if (!$checkout_config['shipping']['ask_zip']) {
                    unset($address['zip']);
                }
                $params['process_result']['data']['shipping']['selected_variant'] = self::$active_shipping['data'];
                $params['process_result']['data']['shipping']['address'] = $address;
            }
        }
    }

    private function getShippingParams($selected_variant)
    {
        $params = array('id' => 0, 'rate_id' => 0);
        if (!empty($selected_variant['selected_variant_id'])) {
            // У Яндекс.Доставки длинные rate_id с точками
            $parts = explode('.', $selected_variant['selected_variant_id'], 2);
            $params['id'] = (int) $parts[0];
            $params['rate_id'] = $parts[1];
        }
        return $params;
    }

    public function checkoutBeforeShipping($params)
    {
        $this->updateContactFieldsFromParams($params['result']);
    }

    private function updateContactFieldsFromParams($params)
    {
        $update = array();
        // Обновляем контактные поля
        if (!empty($params['auth']['fields'])) {
            $update = $params['auth']['fields'];
        }
        // Обновляем поля адреса
        if (!empty($params['region']['selected_values'])) {
            $address = array('address.shipping' => array('country' => $params['region']['selected_values']['country_id'], 'region' => $params['region']['selected_values']['region_id'], 'city' => $params['region']['selected_values']['city']));
            $update += $address;
        }
        if ($update) {
            (new shopDelpayfilterHelper())->updateContact($update);
        }
    }

    public function checkoutRenderShipping($params)
    {
        self::$active_shipping = array();

        // Сохраняем информацию о заказе
        $cache = new waRuntimeCache('delpayfilter_checkout_params');
        $shipping = !empty($params['vars']['shipping']) ? $params['vars']['shipping'] : [];
        $checkout_params = array(
            'shipping' => $this->getShippingParams($shipping),
            'contact' => ''
        );

        // Обновляем контактные поля
        $this->updateContactFieldsFromParams($params['vars']);

        $cache->set($checkout_params);

        if (!empty($params['vars']['shipping']['types'])) {

            // Собираем все методы в массив
            $methods = $this->parseMethods($params['vars']['shipping']['types']);

            // Фильтруем методы
            $output_methods = self::filterDeliveryMethods($methods['ids'], true, true);

            // Удаляем методы на витрине
            $this->hideShippingMethods($methods['methods'], $output_methods, $checkout_params, $params['vars']['shipping'], $params['data']['input'], $params['data']['shipping']);
        }
        $cache->set($checkout_params);
    }

    private function parseMethods($shipping_types)
    {
        $methods = array('pickup' => array(), 'todoor' => array(), 'post' => array());
        $methods_ids = array();
        if (!empty($shipping_types)) {
            foreach ($shipping_types as $k => $type) {
                if (!empty($type['variants'])) {
                    foreach ($type['variants'] as $variant_k => $variant) {
                        $id = explode('.', $variant_k)[0];
                        $methods[$k][$variant_k] = $id;
                        $methods_ids[$id] = $id;
                    }
                }
            }
        }
        return array('methods' => $methods, 'ids' => $methods_ids);
    }

    private function hideShippingMethods($methods, $output_methods, &$checkout_params, &$shipping_vars, &$shipping_input, &$shipping_data = array())
    {
        $change_variant = false;
        foreach ($shipping_vars['types'] as $k => $type) {
            if (!empty($type['variants'])) {
                $rate_min = $rate_max = $date_min_ts = $date_max_ts = $rate_min_value = $rate_max_value = null;
                foreach ($type['variants'] as $variant_k => $variant) {
                    $key = $methods[$k][$variant_k];

                    // Если варианта доставки нет среди отфильтрованных, удаляем вариант
                    if (!isset($output_methods[$key])) {
                        unset($shipping_vars['types'][$k]['variants'][$variant_k]);
                        // Если вариантов больше не осталось, удаляем метод доставки
                        if (empty($shipping_vars['types'][$k]['variants'])) {
                            unset($shipping_vars['types'][$k]);
                        }
                        // Если вариант, который мы удалили, был выбран, заменяем его
                        if ($checkout_params['shipping']['id'] == $key) {
                            $change_variant = true;
                        }
                    } else {
                        $variant_rate_min_value = shop_currency($variant['rate_min'], $variant['currency'], null, false);
                        if ($rate_min === null || ($rate_min !== null && $variant_rate_min_value < $rate_min_value)) {
                            $rate_min = $variant_k;
                            $rate_min_value = $variant_rate_min_value;
                        }
                        $variant_rate_max_value = shop_currency($variant['rate_max'], $variant['currency'], null, false);
                        if ($rate_max === null || ($rate_max !== null && $variant_rate_max_value > $rate_max_value)) {
                            $rate_max = $variant_k;
                            $rate_max_value = $variant_rate_max_value;
                        }
                        if ($date_min_ts === null || ($date_min_ts !== null && $variant['date_min_ts'] < $date_min_ts)) {
                            $date_min_ts = $variant_k;
                        }
                        if ($date_max_ts === null || ($date_max_ts !== null && $variant['date_max_ts'] > $date_max_ts)) {
                            $date_max_ts = $variant_k;
                        }
                    }
                    // Меняем активный способ доставки
                    if ($change_variant) {
                        $this->setShippingActiveVariant($k, $shipping_vars, $shipping_data, $shipping_input, $checkout_params);
                        $change_variant = false;
                    }
                }
                // Если остался один вариант доставки у активного способа, устанавливаем его в качестве активного
                if ($shipping_vars['selected_type_id'] == $k && !empty($shipping_vars['types'][$k]['variants']) && count($shipping_vars['types'][$k]['variants']) === 1) {
                    $this->setShippingActiveVariant($k, $shipping_vars, $shipping_data, $shipping_input, $checkout_params);
                }
                // Меняем доп информацию о времени доставки, миним/макс стоимости и тд
                if (!empty($shipping_vars['types'][$k])) {
                    if (!empty($shipping_vars['types'][$k]['variants'][$rate_min])) {
                        $shipping_vars['types'][$k]['rate_min'] = (float) shop_currency($shipping_vars['types'][$k]['variants'][$rate_min]['rate_min'], $shipping_vars['types'][$k]['variants'][$rate_min]['currency'], $shipping_vars['types'][$k]['currency'], false);
                    }
                    if (!empty($shipping_vars['types'][$k]['variants'][$rate_max])) {
                        $shipping_vars['types'][$k]['rate_max'] = (float) shop_currency($shipping_vars['types'][$k]['variants'][$rate_max]['rate_max'], $shipping_vars['types'][$k]['variants'][$rate_max]['currency'], $shipping_vars['types'][$k]['currency'], false);
                    }
                    if (!empty($shipping_vars['types'][$k]['variants'][$date_min_ts]['delivery_date'])) {
                        $shipping_vars['types'][$k]['date_min'] = $shipping_vars['types'][$k]['variants'][$date_min_ts]['date_min'];
                        $shipping_vars['types'][$k]['date_min_ts'] = $shipping_vars['types'][$k]['variants'][$date_min_ts]['date_min_ts'];
                    }
                    if (!empty($shipping_vars['types'][$k]['variants'][$date_max_ts]['delivery_date'])) {
                        $shipping_vars['types'][$k]['date_max'] = $shipping_vars['types'][$k]['variants'][$date_max_ts]['date_max'];
                        $shipping_vars['types'][$k]['date_max_ts'] = $shipping_vars['types'][$k]['variants'][$date_max_ts]['date_max_ts'];
                    }
                    if (!empty($shipping_vars['types'][$k]['date_min_ts'])) {
                        $shipping_vars['types'][$k]['date_formatted'] = waDateTime::format('humandate', $shipping_vars['types'][$k]['date_min_ts']);
                        if (empty($shipping_vars['types'][$k]['date_max_ts'])
                            || $shipping_vars['types'][$k]['date_max_ts'] != $shipping_vars['types'][$k]['date_min_ts']) {
                            $shipping_vars['types'][$k]['date_formatted'] = _w('from') . ' ' . $shipping_vars['types'][$k]['date_formatted'];
                        }
                    }

                    // Do not show year if reasonable
                    $shipping_vars['types'][$k]['date_formatted'] = str_replace(date('Y'), '', $shipping_vars['types'][$k]['date_formatted']);
                    if ($shipping_vars['types'][$k]['date_min_ts'] - time() < 3600 * 24 * 365 / 2) {
                        $shipping_vars['types'][$k]['date_formatted'] = str_replace(date('Y', strtotime('+1 year')), '', $shipping_vars['types'][$k]['date_formatted']);
                    }
                    $shipping_vars['types'][$k]['date_formatted'] = trim($shipping_vars['types'][$k]['date_formatted'], " ,\t\r\n");
                }
            }
        }
    }

    private function setShippingActiveVariant($type_id, &$shipping_vars, &$shipping_data, &$shipping_input, &$checkout_params)
    {
        if (!empty($shipping_vars['types'][$type_id]['variants'])) {
            $shipping_data['selected_variant'] =
            self::$active_shipping['data'] =
                reset($shipping_vars['types'][$type_id]['variants']);
            $shipping_vars['selected_variant_id'] =
            $shipping_input['shipping']['variant_id'] =
            self::$active_shipping['variant_id'] =
                key($shipping_vars['types'][$type_id]['variants']);
            $checkout_params['shipping']['id'] =
            self::$active_shipping['id'] =
                explode('.', $shipping_vars['selected_variant_id'])[0];
            self::$active_shipping['type'] = $type_id;
        } else {
            $shipping_vars['selected_variant_id'] =
            $shipping_input['shipping']['variant_id'] =
            $shipping_vars['selected_type_id'] =
            $shipping_input['shipping']['type_id'] =
            $shipping_data['selected_variant'] =
            self::$active_shipping['type'] =
            self::$active_shipping['variant_id'] =
            self::$active_shipping['data'] =
            self::$active_shipping['id'] = null;
            $checkout_params['shipping']['id'] = 0;
        }
    }

    public function checkoutRenderPayment($params)
    {
        $checkout_params = array(
            'payment' => 0
        );
        // Сохраняем информацию о заказе
        $cache = new waRuntimeCache('delpayfilter_checkout_params');
        if ($cache->isCached()) {
            $checkout_params += $cache->get();
        }

        if (!empty($params['vars']['payment']['selected_method_id'])) {
            $checkout_params['payment'] = (int) $params['vars']['payment']['selected_method_id'];
        }
        $cache->set($checkout_params);

        if (!empty($params['vars']['payment']['methods'])) {
            $payment_methods = $params['vars']['payment']['methods'];
            // Фильтруем методы
            $output_methods = self::filterPaymentMethods($payment_methods, true, true);

            // Удаляем методы на витрине
            foreach ($payment_methods as $k => $method) {
                if (!isset($output_methods[$k])) {
                    unset($params['vars']['payment']['methods'][$k]);
                    // Если вариант, который мы удалили, был выбран, заменяем его
                    if ($checkout_params['payment'] == $k) {
                        $checkout_params['payment'] = 0;
                    }
                }
            }
        }

        $cache->set($checkout_params);
    }

    /**
     * Filter delivery methods.
     *
     * @param array $methods
     * @param bool $force
     * @param bool $remove_on_error - remove method, if error field exists
     * @return array
     * @throws waException
     */
    public static function filterDeliveryMethods($methods, $force = false, $remove_on_error = false)
    {
        $filter_methods = self::getFailedMethods($force);
        foreach ($methods as $k => $m) {
            if (isset($filter_methods['delivery'][$k])) {
                if ($filter_methods['delivery'][$k] && !$remove_on_error && is_array($methods[$k])) {
                    $methods[$k]['error'] = $filter_methods['delivery'][$k];
                } else {
                    unset($methods[$k]);
                }
            }
        }
        return $methods;
    }

    /**
     * Filter payment methods.
     *
     * @param array $methods
     * @param bool $force
     * @param bool $remove_on_error - remove method, if error field exists
     * @return array
     * @throws waException
     */
    public static function filterPaymentMethods($methods, $force = false, $remove_on_error = false)
    {
        $filter_methods = self::getFailedMethods($force);
        foreach ($methods as $k => $m) {
            if (isset($filter_methods['payment'][$k])) {
                if ($filter_methods['payment'][$k] && !$remove_on_error) {
                    $methods[$k]['error'] = $filter_methods['payment'][$k];
                } else {
                    unset($methods[$k]);
                }
            }
        }
        return $methods;
    }

    /**
     * Methods that we need to hide
     *
     * @param bool $force
     * @return array
     * @throws waException
     */
    public static function getFailedMethods($force = false)
    {
        $filters = shopDelpayfilterHelper::getFilters();
        return shopDelpayfilterCore::getMethods($filters, $force);
    }

}
