<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountCore extends shopFlexdiscountConditions
{

    // Список товаров, получивших скидки
    private static $discount_products = array();
    // Список правил, участвующих в скидках
    private static $active_rules = array();
    // Список запрещающих правил
    private static $deny_rules = array();

    /**
     * Calculate discount to order
     *
     * @param array $params
     * @param array $groups
     * @return array
     * @throws waException
     */
    public static function calculate_discount($params, $groups)
    {
        // Очищаем данные при новом пересчете
        self::cleanStaticVariables();

        self::$enable_filter_hook = (new waAppSettingsModel())->get(array('shop', 'flexdiscount'), shopFlexdiscountProfile::SETTINGS['FILTER_HOOK']);

        $total_discount = $total_affiliate = 0;
        $group_discounts = array();
        $items = $params['order']['items'];

        self::$user = wa()->getEnv() == 'backend' ? $params['contact'] : (new shopFlexdiscountHelper())->getContact(true);

        self::setOrderInfo($params['order']);

        // Необходимо для бекенда при случае создания нового заказа
        if (wa()->getEnv() == 'backend') {
            self::setOrder($params);
        }

        // Выполняем предварительную обработку товаров
        shopFlexdiscountHelper::workupProducts($items);

        self::$deny_rules = self::getDenyRules($groups, $items);

        // Получаем информацию о купонах
        $coupon_info = shopFlexdiscountCouponPluginModel::couponCheck(isset($params['order']['id']) ? $params['order']['id'] : 0);

        // Выполняем перебор групп скидок
        foreach ($groups as $group_id => $group) {
            $result_items = array();
            $rules = $group_id === 0 ? $group : $group['items'];
            foreach ($rules as $k => $rule) {
                if ($rule['status']) {
                    // Проверяем наличие купонов у правил скидок
                    if (!empty($rule['enable_coupon']) || !empty($rule['rule_has_coupon'])) {
                        // Если купон не введен, то прерываем обработку правила
                        if (!$coupon_info && !empty($rule['enable_coupon'])) {
                            continue;
                        }
                        if ($coupon_info) {
                            // Сохраняем купоны, которые сработали для правила скидок
                            foreach ($coupon_info as $c_id => $c) {
                                if (isset($c['coupon_rules'][$rule['id']])) {
                                    $rules[$k]['active_coupon'] = $c_id;
                                    $rules[$k]['active_coupon_code'] = $c['code'];
                                }
                            }
                            // Если не сработало ни одного купона, прерываем обработку правила
                            if (!isset($rules[$k]['active_coupon'])) {
                                continue;
                            }
                        }
                    }

                    $all_items = $items;
                    // Проверяем наличие запрещающих правил, которые выбрасывают товары из расчетов
                    if (!empty(self::$deny_rules[$group_id]['drop_from_calc'])) {
                        $all_items = self::dropDenyItems($all_items, self::$deny_rules[$group_id]['drop_from_calc'], $group_id);
                    }
                    if ($group_id !== 0 && !empty(self::$deny_rules[0]['drop_from_calc'])) {
                        // Применяем общее правило запрета
                        $all_items = self::dropDenyItems($all_items, self::$deny_rules[0]['drop_from_calc']);
                    }

                    self::getAllItems($all_items);

                    // Запоминаем, обрабатывается комплект или нет
                    shopFlexdiscountData::isBundle($rule);
                    // Условия скидок
                    $conditions = self::decode($rule['conditions']);
                    // Товары, удовлетворяющие условиям
                    $result_items[$rule['id']] = $conditions ? self::filter_items($all_items, $conditions->group_op, $conditions->conditions) : $all_items;
                }
            }
            // Размер скидки и бонусов для группы
            $total_result = self::get_discount($result_items, $items, isset($group['combine']) ? $group['combine'] : 'sum', $rules, $group_id);
            if ($total_result['discount']) {
                $group_discounts[$group_id] = array(
                    "discount" => $total_result['discount'],
                    "affiliate" => $total_result['affiliate'],
                    "rule_ids" => $total_result['active_rule_ids']
                );
            }
        }
        // Проводим финальный расчет скидок
        self::combineGroupDiscounts($group_discounts);
        foreach (self::$active_rules as $ar) {
            $total_discount += $ar['discount'];
            $total_affiliate += $ar['affiliate'];
        }

        return array(
            "discount" => $total_discount,
            "affiliate" => $total_affiliate,
            "rule_products" => self::$discount_products,
            "products" => self::prepareDiscountProducts(),
            "active_rules" => shopFlexdiscountHelper::sortRules(self::$active_rules)
        );
    }

    /**
     * Calculate target discount
     *
     * @param array $target
     * @param array $discount_items
     * @param array $rule
     * @return array Discount and affiliate
     */
    private static function calculate_target_discount($target, $discount_items, $rule)
    {
        $discount = $quantity = $affiliate = 0;
        /* Выполняем дополнительную обработку товаров */
        // Обрабатываем комплект
        if (shopFlexdiscountData::isBundle($rule)) {
            $discount_items = self::bundleFilter($discount_items, $target, $rule);
        }
        // Выполняем уточнения
        if (!empty($target->details->field) && !empty($target->details->value)) {
            $discount_items = self::detailsFilter($target->details, $discount_items);
        }

        if (empty($discount_items)) {
            return array("discount" => 0, "affiliate" => 0);
        }

        // Если перед нами запрещающее правило, то возвращаем массив отобранных товаров
        if (!empty($rule['deny'])) {
            return $discount_items;
        }

        $primary_currency = wa('shop')->getConfig()->getCurrency(true);
        // Скидка в валюте
        $rule_discount = $target->target !== 'shipping' ? (!empty($rule['discount']) ? $rule['discount'] : 0) : (!empty($rule['discount_shipping']) ? $rule['discount_shipping'] : (!isset($rule['discount_shipping']) && !empty($rule['discount']) ? $rule['discount'] : 0));
        $rule_currency = $target->target !== 'shipping' ? (!empty($rule['discount_currency']) ? $rule['discount_currency'] : '') : (!empty($rule['discount_shipping_currency']) ? $rule['discount_shipping_currency'] : (!isset($rule['discount_shipping_currency']) && !empty($rule['discount_currency']) ? $rule['discount_currency'] : 0));
        $rule['discount'] = !empty($rule_discount) && $rule_discount > 0 ? (float) shop_currency($rule_discount, (!empty($rule_currency) ? $rule_currency : $primary_currency), self::getOrderInfo('currency'), false) : 0;
        // Бонусы
        $rule_affiliate = $target->target !== 'shipping' ? (!empty($rule['affiliate']) ? $rule['affiliate'] : 0) : (!empty($rule['affiliate_shipping']) ? $rule['affiliate_shipping'] : (!isset($rule['affiliate_shipping']) && !empty($rule['affiliate']) ? $rule['affiliate'] : 0));
        $rule['affiliate'] = !empty($rule_affiliate) && $rule_affiliate > 0 ? shopFlexdiscountHelper::floatVal($rule_affiliate) : 0;

        // Вычисляем скидку для каждого товара
        if ($target->target !== 'shipping') {
            foreach ($discount_items as $item_id => $item) {
                // Пропускаем абстрактный товар и товары, на которые уже была начислена скидка.
                if ($item['sku_id'] == 0 || isset(self::$discount_products[$rule['id']][$item['sku_id']])) {
                    continue;
                }

                // Размер скидки товара
                $product_discount = !empty($rule['discount_percentage']) ? max(0.0, min(100.0, shopFlexdiscountHelper::floatVal($rule['discount_percentage']))) * self::getRuleBase($item, $rule) / 100.0 : 0;
                $product_discount = shop_currency($product_discount, self::getOrderInfo('currency'), self::getOrderInfo('currency'), false);
                // Размер бонусов, начисленных на товар
                $affiliate_value = self::getRuleBase($item, $rule, 'affiliate_base');
                $product_affiliate = !empty($rule['affiliate_percentage']) ? round(max(0.0, min(100.0, shopFlexdiscountHelper::floatVal($rule['affiliate_percentage']))) * (float) shop_currency($affiliate_value, self::getOrderInfo('currency'), $primary_currency, false) / 100.0, 2) : 0;

                // Устанавливаем скидку на каждый товар
                if ($rule['discount'] > 0 && !empty($rule['discounteachitem'])) {
                    $product_discount += $rule['discount'];
                }

                // Начисляем бонусы на каждый товар
                if ($rule['affiliate'] > 0 && !empty($rule['affiliateeachitem'])) {
                    $product_affiliate += $rule['affiliate'];
                }

                // Округление
                $product_discount = shopFlexdiscountHelper::round($product_discount) * $item['quantity'];
                $product_affiliate = shopFlexdiscountHelper::round($product_affiliate, '', 'affiliate') * $item['quantity'];

                // Если товар не получил ни скидки, ни бонуса, то не сохраняем его
                if (!$product_discount && !$product_affiliate) {
                    continue;
                }

                // Цена товара с учетом количества
                $full_product_price = $item['price'] * $item['quantity'];

                // Цена со скидкой. Вычисляется исходя из формулы.
                $price_with_discount = self::getRuleBase($item, $rule, 'discount_base_main') * $item['quantity'] - $product_discount;
                // Если значение цены со скидкой больше, чем цена товара, тогда скидка равна 0
                if ($price_with_discount > $full_product_price) {
                    continue;
                }
                $product_discount = $full_product_price - $price_with_discount;

                // Не даем скидку больше, чем цена товара
                if ($product_discount > $full_product_price) {
                    $product_discount = $full_product_price;
                }

                // Инициализируем товар, участвующий в скидках и получивший бонусы
                self::initRuleProduct($rule['id'], $item['sku_id'], $item_id);

                // Запоминаем размер скидки для каждого товара и кол-во товаров, участвующих в скидках
                self::$discount_products[$rule['id']][$item['sku_id']]['quantity'] += shopFlexdiscountHelper::floatVal($item['quantity']);
                self::$discount_products[$rule['id']][$item['sku_id']]['discount'] += shopFlexdiscountHelper::floatVal($product_discount);
                self::$discount_products[$rule['id']][$item['sku_id']]['affiliate'] += shopFlexdiscountHelper::floatVal($product_affiliate);
                // Общее значение скидки
                $discount += $product_discount;
                // Общее значение бонусов
                $affiliate += $product_affiliate;
            }
        } else {
            // Рассчитываем скидку для способов доставки
            $shipping_percentage = !empty($rule['discount_shipping_percentage']) ? $rule['discount_shipping_percentage'] : (!isset($rule['discount_shipping_percentage']) && !empty($rule['discount_percentage']) ? $rule['discount_percentage'] : 0);
            $discount += !empty($shipping_percentage) ? max(0.0, min(100.0, shopFlexdiscountHelper::floatVal($shipping_percentage))) * $target->base / 100.0 : 0;
            // Размер бонусов, начисленных на товар
            $shipping_affiliate_percentage = !empty($rule['affiliate_shipping_percentage']) ? $rule['affiliate_shipping_percentage'] : (!isset($rule['affiliate_shipping_percentage']) && !empty($rule['affiliate_percentage']) ? $rule['affiliate_percentage'] : 0);
            $affiliate = !empty($shipping_affiliate_percentage) ? max(0.0, min(100.0, shopFlexdiscountHelper::floatVal($shipping_affiliate_percentage))) * $target->primary_total / 100.0 : 0;
        }
        // Устанавливаем скидку на весь заказ
        if ($rule['discount'] > 0 && ((empty($rule['discounteachitem']) && empty($rule['discounteachbundle'])) || $target->target == 'shipping')) {
            $rule['discount'] = shopFlexdiscountHelper::round($rule['discount']);
            $discount += $rule['discount'];
        }
        // Начисляем бонусы на весь заказ
        if ($rule['affiliate'] > 0 && ((empty($rule['affiliateeachitem']) && empty($rule['affiliateeachbundle'])) || $target->target == 'shipping')) {
            $rule['affiliate'] = shopFlexdiscountHelper::round($rule['affiliate'], '', 'affiliate');
            $affiliate += $rule['affiliate'];
        }
        // Работаем с комплектами
        if (!empty($item['bundle_mult'])) {
            // Устанавливаем скидку на комплект
            if ($rule['discount'] > 0 && !empty($rule['discounteachbundle'])) {
                $rule['discount'] = shopFlexdiscountHelper::round($rule['discount']) * $item['bundle_mult'];
                $discount += $rule['discount'];
            }
            // Начисляем бонусы на комплект
            if ($rule['affiliate'] > 0 && !empty($rule['affiliateeachbundle'])) {
                $rule['affiliate'] = shopFlexdiscountHelper::round($rule['affiliate'], '', 'affiliate') * $item['bundle_mult'];
                $affiliate += $rule['affiliate'];
            }
        }

        $result = array("discount" => $discount, "affiliate" => $affiliate);
        // Не даем скидку и бонусов больше, чем стоимость доставки
        if ($target->target == 'shipping') {
            if ($discount >= $target->total) {
                $result['discount'] = $target->total;
                $result['free_shipping'] = 1;
            }
            if ($affiliate > $target->total) {
                $result['affiliate'] = $target->total;
            }
        }

        return $result;
    }

    /**
     * Limit discount value
     *
     * @param float $discount
     * @param array $rule
     * @param array $items
     * @return float
     * @throws waException
     */
    private static function limitDiscountValue($discount, $rule, $items)
    {
        // Ограничение скидки для товара
        if (!empty($rule['limit']['status'])) {
            $limit = $rule['limit'];
            foreach ($items as $item) {
                // Собираем минимальное значение цены товара
                $product_price_minimum = 0;
                // Ограничение скидок по формуле
                if (
                    !empty($limit['value']) && $limit['value'] >= 0
                    && (
                        (!empty($limit['type']) && $limit['type'] == 'formula')
                        || !isset($limit['type'])
                    )
                ) {
                    switch ($limit['price1']) {
                        case "purchase":
                            $product_price_minimum += $item['purchase_price'];
                            break;
                        case "compare_price":
                            $product_price_minimum += $item['compare_price'];
                            break;
                    }
                    if ($limit['currency'] == '%') {
                        $price2 = 0;
                        switch ($limit['price2']) {
                            case "purchase":
                                $price2 += $item['purchase_price'];
                                break;
                            case "price":
                                $price2 += $item['price'];
                                break;
                            case "compare_price":
                                $price2 += $item['compare_price'];
                                break;
                        }
                        $product_price_minimum += max(0.0, min(100.0, (float) $limit['value'])) * $price2 / 100;
                    } else {
                        $product_price_minimum += (float) shop_currency((float) $limit['value'], $limit['currency'], self::getOrderInfo('currency'), false);
                    }
                } // Ограничение скидок по полю у артикула
                elseif (!empty($item['flexdiscount_minimal_discount_price'])) {
                    $product_price_minimum = (float) shop_currency((float) $item['flexdiscount_minimal_discount_price'], $item['flexdiscount_minimal_discount_currency'], self::getOrderInfo('currency'), false);
                }

                // Если товар участвовал в скидках
                if (isset(self::$discount_products[$rule['id']][$item['sku_id']])) {
                    // Размер скидки для одной единицы товара
                    $product_discount = self::$discount_products[$rule['id']][$item['sku_id']]['discount'] / self::$discount_products[$rule['id']][$item['sku_id']]['quantity'];
                    // Если размер скидки для товара больше, чем установлено ограничением
                    if ($item['price'] - $product_discount < $product_price_minimum) {
                        // Меняем скидку для товара
                        self::$discount_products[$rule['id']][$item['sku_id']]['discount'] = shopFlexdiscountHelper::round($item['price'] - $product_price_minimum) * self::$discount_products[$rule['id']][$item['sku_id']]['quantity'];
                        // Если скидка товара ниже 0, это означает, что минимальная цена товара получилась выше самой цены товара.
                        // Удаляем товар из массива скидок
                        if (self::$discount_products[$rule['id']][$item['sku_id']]['discount'] <= 0) {
                            $discount -= $product_discount * self::$discount_products[$rule['id']][$item['sku_id']]['quantity'];
                            unset(self::$discount_products[$rule['id']][$item['sku_id']]);
                        } else {
                            // Меняем общую скидку
                            $discount -= ($product_discount * self::$discount_products[$rule['id']][$item['sku_id']]['quantity'] - self::$discount_products[$rule['id']][$item['sku_id']]['discount']);
                        }
                    }
                }
            }
        }

        // Максимальное значение скидки
        if (!empty($rule['maximum']['value']) && !empty($rule['maximum']['currency'])) {
            $maximum = (float) shop_currency($rule['maximum']['value'], $rule['maximum']['currency'], self::getOrderInfo('currency'), false);
            if ($maximum < $discount) {
                $discount = $maximum;
                // Уменьшаем значения скидок для каждого товара
                if (!empty(self::$discount_products[$rule['id']])) {
                    $delete_products = false;
                    foreach (self::$discount_products[$rule['id']] as $k => $sku) {
                        if ($delete_products) {
                            unset(self::$discount_products[$rule['id']][$k]);
                            continue;
                        }
                        if ($sku['discount'] > $maximum) {
                            self::$discount_products[$rule['id']][$k]['discount'] = $maximum;
                            $delete_products = true;
                        } else {
                            $maximum -= $sku['discount'];
                        }
                    }
                }
            }
        }

        return $discount;
    }

    /**
     * Limit affiliate value
     *
     * @param float $affiliate
     * @param array $rule
     * @return float
     */
    private static function limitAffiliateValue($affiliate, $rule)
    {
        // Максимальное значение бонусов для каждого товара
        $maximum_product_affiliate = (isset($rule['maximum_product_affiliate']) && $rule['maximum_product_affiliate'] !== '') ? shopFlexdiscountHelper::floatVal($rule['maximum_product_affiliate']) : null;
        if ($maximum_product_affiliate !== null) {
            if (!empty(self::$discount_products[$rule['id']])) {
                foreach (self::$discount_products[$rule['id']] as $k => $sku) {
                    // Размер бонусов для одной единицы товара
                    $product_affiliate = $sku['affiliate'] / $sku['quantity'];
                    // Если размер бонусов для товара больше, чем установлено ограничением
                    if ($product_affiliate > $maximum_product_affiliate) {
                        // Меняем бонусы для товара
                        self::$discount_products[$rule['id']][$k]['affiliate'] = $maximum_product_affiliate * $sku['quantity'];
                        $affiliate -= ($product_affiliate * $sku['quantity'] - self::$discount_products[$rule['id']][$k]['affiliate']);
                    }
                }
            }
        }

        // Максимальное значение бонусов для правила
        if (!empty($rule['maximum_affiliate'])) {
            if ($rule['maximum_affiliate'] < $affiliate) {
                $affiliate = $rule['maximum_affiliate'];
                // Уменьшаем значения бонусов для каждого товара
                if (!empty(self::$discount_products[$rule['id']])) {
                    $reset_bonus = false;
                    foreach (self::$discount_products[$rule['id']] as $k => $sku) {
                        if ($reset_bonus) {
                            self::$discount_products[$rule['id']][$k]['affiliate'] = 0;
                        }
                        if ($sku['affiliate'] > $rule['maximum_affiliate']) {
                            self::$discount_products[$rule['id']][$k]['affiliate'] = $rule['maximum_affiliate'];
                            $reset_bonus = true;
                        } else {
                            $rule['maximum_affiliate'] -= $sku['affiliate'];
                        }
                    }
                }
            }
        }

        return $affiliate;
    }

    /**
     * Get group discount
     *
     * @param array $result_items - items after conditions
     * @param array $items - all cart items
     * @param string $combine - combine rule
     * @param array $rules - all rules
     * @param int $group_id
     * @return array
     * @throws waException
     */
    private static function get_discount($result_items, $items, $combine, $rules, $group_id)
    {
        static $instance = null;
        if ($instance === null) {
            $instance = get_class();
        }

        // ID активных правил скидок для группы
        $group_discount_ids = array();
        $discount = $combine_rule = $affiliate = 0;

        // Совершаем перебор всех скидок
        foreach ($result_items as $rule_id => $filter_items) {
            // Если скидки не существует, пропускаем
            if (!isset($rules[$rule_id])) {
                continue;
            }

            $original_filter_items = $filter_items;
            // Проверяем наличие запрещающих правил
            if (!empty(self::$deny_rules[$group_id])) {
                $items = self::dropDenyItems($items, self::$deny_rules[$group_id]['all'], $group_id);
                $filter_items = self::dropDenyItems($filter_items, self::$deny_rules[$group_id]['all'], $group_id);
            }
            if ($group_id !== 0 && !empty(self::$deny_rules[0])) {
                // Применяем общее правило запрета
                $items = self::dropDenyItems($items, self::$deny_rules[0]['all']);
                $filter_items = self::dropDenyItems($filter_items, self::$deny_rules[0]['all']);
            }

            $rule = $rules[$rule_id];
            $target = self::decode($rule['target']);
            $target_discount = $target_affiliate = 0;
            $free_shipping = 0;
            if ($target) {
                // Назначаем скидки на выбранные объекты
                foreach ($target as $t) {
                    $function_name = 'target_' . $t->target;
                    if (method_exists($instance, $function_name)) {
                        // Общее значение скидки и бонусов для правила
                        $target_result = self::$function_name($t->target == 'shipping' ? $original_filter_items : $filter_items, $items, $t, $rule);
                        $target_discount += $target_result['discount'];
                        $target_affiliate += $target_result['affiliate'];
                        if ($t->target == 'shipping' && !empty($target_result['free_shipping'])) {
                            $free_shipping = $target_result['discount'];
                        }
                    }
                }
            }

            // Ограничение скидок для правила
            $target_discount = self::limitDiscountValue($target_discount, $rule, $items);
            // Ограничение бонусов для правила
            $target_affiliate = self::limitAffiliateValue($target_affiliate, $rule);

            // Значение скидки в зависимости от расчета (сумма, максимум, минимум)
            $combine_result = self::combine_filter($combine, $target_discount, $discount, $rule_id);
            // Значение бонусов в зависимости от расчета (сумма, максимум, минимум)
            $combine_result_affiliate = self::combine_filter($combine, $target_affiliate, $affiliate, $rule_id);

            if ($combine !== 'sum' && $combine !== 'mpr') {
                // Для максимума и минимума вычисляем подходящее значение
                if (is_array($combine_result) || is_array($combine_result_affiliate)) {
                    $discount = !empty($combine_result) ? $combine_result['value'] : 0;
                    $combine_rule = !empty($combine_result) ? $combine_result['rule_id'] : (!empty($combine_result_affiliate) ? $combine_result_affiliate['rule_id'] : 0);
                    $affiliate = !empty($combine_result_affiliate) ? $combine_result_affiliate['value'] : 0;

                    if ($discount && $free_shipping && $free_shipping <= $discount) {
                        $rules[$combine_rule]['free_shipping'] = $free_shipping;
                        $discount -= $free_shipping;
                    }
                }
            } else {
                $discount = $combine_result;
                $affiliate = $combine_result_affiliate;

                if ($target_discount || $target_affiliate) {

                    if ($target_discount && $free_shipping && $free_shipping <= $target_discount) {
                        $rule['free_shipping'] = $free_shipping;
                        $target_discount -= $free_shipping;
                        $discount -= $free_shipping;
                    }

                    $group_discount_ids[$rule['id']] = $rule['id'];
                    self::addToActiveRule($rule, $target_discount, $target_affiliate);
                }
            }
        }

        if ($combine !== 'sum' && $combine !== 'mpr') {
            if (!empty($combine_rule) && ($discount || $affiliate || (!$discount && !empty($rules[$combine_rule]['free_shipping'])))) {
                $group_discount_ids[$rules[$combine_rule]['id']] = $rules[$combine_rule]['id'];
                self::addToActiveRule($rules[$combine_rule], $discount, $affiliate);
            }

            // Удаляем товары, не участвующие в скидках и бонусах
            $result_items_copy = $result_items;
            unset($result_items_copy[$combine_rule]);
            self::cleanDiscountProducts(array_keys($result_items_copy));
        } elseif ($combine == 'mpr') {
            self::maxProductCombineFilter(array_keys($rules));
        }
        return array("discount" => $discount, "affiliate" => $affiliate, "active_rule_ids" => $group_discount_ids);
    }

    /**
     * Add discount to array of active rules
     *
     * @param array $rule
     * @param float $discount
     * @param float $affiliate
     */
    private static function addToActiveRule($rule, $discount, $affiliate)
    {
        // Информация о примененной скидке
        self::$active_rules[$rule['id']] = array(
            "name" => $rule['name'] ? $rule['name'] : _wp("Discount #") . $rule['id'],
            "description" => $rule['description'] ? $rule['description'] : '',
            "code" => $rule['code'],
            "sort" => $rule['sort'],
            "discount" => $discount,
            "affiliate" => $affiliate ? $affiliate : 0,
            "coupon_id" => !empty($rule['active_coupon']) ? $rule['active_coupon'] : 0,
            "clean_coupon" => !empty($rule['clean_coupon']) ? 1 : 0,
            "full_info" => $rule,
            "free_shipping" => !empty($rule['free_shipping']) ? $rule['free_shipping'] : 0
        );
        if (self::$active_rules[$rule['id']]["coupon_id"]) {
            self::$active_rules[$rule['id']]["coupon_code"] = $rule['active_coupon_code'];
        }
    }

    /**
     * Discount value after combine filter
     *
     * @param string $combine
     * @param float $target_value
     * @param float $total_value
     * @param int $rule_id
     * @return float|array
     */
    private static function combine_filter($combine, $target_value, $total_value, $rule_id)
    {
        $result = 0;
        switch ($combine) {
            // Расчет скидок по максимальному значению
            case "max":
                if ($target_value > $total_value) {
                    $result = array("value" => $target_value, "rule_id" => $rule_id);
                }
                break;
            // Расчет скидок по минимальному значению
            case "min":
                if ($target_value < $total_value || $total_value === 0) {
                    $result = array("value" => $target_value, "rule_id" => $rule_id);
                }
                break;
            default:
                $result += $target_value + $total_value;
        }
        return $result;
    }

    /**
     * Maximum product group combine filter
     *
     * @param array $rule_ids
     */
    private static function maxProductCombineFilter($rule_ids)
    {
        $products = array();
        if (self::$discount_products) {
            foreach (self::$discount_products as $rule_id => $sku) {
                // Пропускаем правила, которые не входят в группу, по которой нужно осуществить расчет МАКС ТОВ
                if (!in_array($rule_id, $rule_ids)) {
                    continue;
                }
                foreach ($sku as $sku_id => $s) {
                    if (!isset($products[$sku_id])) {
                        $products[$sku_id] = array(
                            'rule_id' => $rule_id,
                            'discount' => $s['discount'],
                            'affiliate' => $s['affiliate'],
                        );
                    } else {
                        // Если имеется бОльшая скидка 
                        if ((self::$active_rules[$rule_id]['discount'] > 0 && $products[$sku_id]['discount'] < $s['discount']) || (self::$active_rules[$rule_id]['discount'] <= 0 && self::$active_rules[$rule_id]['affiliate'] > 0 && $products[$sku_id]['affiliate'] < $s['affiliate'])) {
                            // Вычитаем меньшую скидку из правил
                            self::$active_rules[$products[$sku_id]['rule_id']]['discount'] -= $products[$sku_id]['discount'];
                            self::$active_rules[$products[$sku_id]['rule_id']]['affiliate'] -= $products[$sku_id]['affiliate'];
                            // Если у правила больше нет скидок, удаляем его
                            if (round(self::$active_rules[$products[$sku_id]['rule_id']]['discount'], 4) <= 0 && round(self::$active_rules[$products[$sku_id]['rule_id']]['affiliate'], 4) <= 0) {
                                unset(self::$active_rules[$products[$sku_id]['rule_id']]);
                            }
                            // Удаляем меньшую скидку
                            unset(self::$discount_products[$products[$sku_id]['rule_id']][$sku_id]);
                            // Заменяем данные на бОльшую скидку
                            $products[$sku_id]['rule_id'] = $rule_id;
                            $products[$sku_id]['discount'] = $s['discount'];
                        } else {
                            // Вычитаем меньшую скидку из правил
                            self::$active_rules[$rule_id]['discount'] -= $s['discount'];
                            self::$active_rules[$rule_id]['affiliate'] -= $s['affiliate'];
                            // Если у правила больше нет скидок, удаляем его
                            if (round(self::$active_rules[$rule_id]['discount'], 4) <= 0 && round(self::$active_rules[$rule_id]['affiliate'], 4) <= 0) {
                                unset(self::$active_rules[$rule_id]);
                            }
                            // Удаляем меньшую скидку
                            unset(self::$discount_products[$rule_id][$sku_id]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Final discount calculating
     *
     * @param array $group_discounts
     * @throws waException
     */
    private static function combineGroupDiscounts($group_discounts)
    {
        $settings = shopFlexdiscountHelper::getSettings();
        if ($group_discounts && !empty($settings['combine_type']) && $settings['combine_type'] !== 'sum') {
            // Выносим общие правила из группы
            if (isset($group_discounts[0])) {
                foreach ($group_discounts[0]['rule_ids'] as $rule_id) {
                    $group_discounts['r' . $rule_id] = array(
                        'discount' => self::$active_rules[$rule_id]['discount'],
                        'affiliate' => self::$active_rules[$rule_id]['affiliate'],
                        'rule_ids' => array($rule_id => $rule_id)
                    );
                }
                unset($group_discounts[0]);
            }
            $filter_discount = key($group_discounts);
            // Получаем ID отфильтрованной группы
            foreach ($group_discounts as $group_id => $gd) {
                if ($settings['combine_type'] == 'max') {
                    $filter_discount = $group_discounts[$filter_discount]['discount'] < $gd['discount'] ? $group_id : $filter_discount;
                } elseif ($settings['combine_type'] == 'min') {
                    $filter_discount = $gd['discount'] < $group_discounts[$filter_discount]['discount'] ? $group_id : $filter_discount;
                }
            }
            // Удаляем все ненужные правила
            foreach (self::$active_rules as $rule_id => $r) {
                if (!isset($group_discounts[$filter_discount]['rule_ids'][$rule_id])) {
                    unset(self::$active_rules[$rule_id], self::$discount_products[$rule_id]);
                }
            }
        }
    }

    /**
     * Init discount and affiliate product
     *
     * @param int $rule_id
     * @param int $sku_id
     * @param int $item_id
     */
    private static function initRuleProduct($rule_id, $sku_id, $item_id = 0)
    {
        if (!isset(self::$discount_products[$rule_id][$sku_id])) {
            self::$discount_products[$rule_id][$sku_id] = array(
                'quantity' => 0,
                'discount' => 0,
                'affiliate' => 0,
                'item_id' => $item_id
            );
        }
    }

    /**
     * Clean discount products.
     *
     * @param array $rules_to_delete
     */
    private static function cleanDiscountProducts($rules_to_delete)
    {
        if ($rules_to_delete) {
            foreach ($rules_to_delete as $rd) {
                if (isset(self::$discount_products[$rd])) {
                    unset(self::$discount_products[$rd]);
                }
            }
        }
    }

    /**
     * Get deny rules with deny products
     *
     * @param array $groups
     * @param array $items
     * @return array
     */
    protected static function getDenyRules(&$groups, $items)
    {
        static $instance = null;
        if ($instance === null) {
            $instance = get_class();
        }
        $result = $result_items = array();
        foreach ($groups as $group_id => $group) {
            $rules = $group_id === 0 ? $group : $group['items'];
            $result[$group_id] = array();
            foreach ($rules as $rule) {
                if ($rule['deny'] && $rule['status']) {
                    // Условия
                    $conditions = self::decode($rule['conditions']);

                    // Товары, удовлетворяющие условиям
                    $result_items = $conditions ? self::filter_items($items, $conditions->group_op, $conditions->conditions) : $items;
                    if ($result_items) {
                        $target = self::decode($rule['target']);
                        foreach ($target as $t) {
                            $function_name = 'target_' . $t->target;
                            if (method_exists($instance, $function_name)) {
                                // Товары, на которые скидка не распространяется
                                $deny_items = self::$function_name($result_items, $items, $t, $rule);
                                if ($deny_items) {
                                    if (!isset($result[$group_id]['use_in_calc'])) {
                                        $result[$group_id] = array('use_in_calc' => array(), 'drop_from_calc' => array(), 'all' => array());
                                    }
                                    // Куда определить товар: в массив товаров, которые учитываются при расчетах скидок или нет
                                    if (!empty($rule['use_in_calc'])) {
                                        $result[$group_id]['use_in_calc'] += $deny_items;
                                    } else {
                                        $result[$group_id]['drop_from_calc'] += $deny_items;
                                    }
                                    $result[$group_id]['all'] += $deny_items;
                                }
                            }
                        }
                    }
                    // Удаляем правла по запрету скидок из общего потока
                    if ($group_id === 0) {
                        unset($groups[$group_id][$rule['id']]);
                    } else {
                        unset($groups[$group_id]['items'][$rule['id']]);
                    }
                }
            }
            if (!$result[$group_id]) {
                unset($result[$group_id]);
            }
        }
        return $result;
    }

    /**
     * Return discount/affiliate base
     *
     * @param array $item
     * @param array $rule
     * @param string $type
     * @return float
     */
    protected static function getRuleBase($item, $rule, $type = 'discount_base')
    {
        if (!empty($rule[$type])) {
            if ($rule[$type] == 'compare_price' && !empty($item['compare_price'])) {
                return $item['compare_price'];
            } elseif ($rule[$type] == 'purchase' && !empty($item['purchase_price'])) {
                return $item['purchase_price'];
            } elseif ($rule[$type] == 'prodpurch') {
                return $item['price'] - (!empty($item['purchase_price']) ? $item['purchase_price'] : 0);
            } elseif ($rule[$type] == 'prodcomp' && !empty($item['compare_price']) && $item['compare_price'] > $item['price']) {
                return $item['compare_price'] - $item['price'];
            }
        }
        return $item['price'];
    }

    /**
     * Drop deny items from array
     *
     * @param array $items
     * @param array $deny_items
     * @param int $deny_group_id
     *
     * @return array
     * @throws waException
     */
    protected static function dropDenyItems($items, $deny_items, $deny_group_id = 0)
    {
        foreach ($deny_items as $k => $v) {
            // Если существует товар и у него еще не происходило уменьшение остатков,
            // понижаем кол-во остатков у товара на кол-во согласно правилу запрета.
            // Это необходимо, если в правиле запрета указано конкретное кол-во товара, для которого скидка не должна действовать
            if (isset($items[$k]) && !isset($items[$k]['deny_group_id'])) {
                $decrease_quantity = shopFlexdiscountHelper::floatVal($v['quantity']);
                $decrease_price = shopFlexdiscountHelper::floatVal($v['price']);
                $items[$k]['quantity'] = shopFlexdiscountHelper::floatVal($items[$k]['quantity']) - $decrease_quantity;
                if ($items[$k]['quantity'] <= 0) {
                    $decrease_quantity += $items[$k]['quantity'];
                    unset($items[$k]);
                } else {
                    $items[$k]['deny_group_id'] = $deny_group_id;
                }
                // Понижаем значение total у заказа
                $order = self::getOrderInfo();
                $order['total'] -= $decrease_price * $decrease_quantity;
                self::setOrderInfo($order);
            }
        }
        return $items;
    }

    private static function cleanStaticVariables()
    {
        self::$discount_products = self::$active_rules = self::$deny_rules = array();
    }

    private static function prepareDiscountProducts()
    {
        $products = array();
        if (self::$discount_products) {
            foreach (self::$discount_products as $rule_id => $sku) {
                foreach ($sku as $sku_id => $s) {
                    if (!isset($products[$sku_id]) && isset($s['item_id'])) {
                        $products[$sku_id] = array(
                            "total_affiliate" => $s['affiliate'],
                            "total_discount" => $s['discount'],
                            "affiliate" => $s['affiliate'] / $s['quantity'],
                            "discount" => $s['discount'] / $s['quantity'],
                            "item_id" => $s['item_id'],
                            "rules" => array($rule_id => $s)
                        );
                    } else {
                        $products[$sku_id]['total_discount'] += $s['discount'];
                        $products[$sku_id]['total_affiliate'] += $s['affiliate'];
                        $products[$sku_id]['discount'] += $s['discount'] / $s['quantity'];
                        $products[$sku_id]['affiliate'] += $s['affiliate'] / $s['quantity'];
                        $products[$sku_id]['rules'][$rule_id] = $s;
                    }
                    unset($products[$sku_id]['rules'][$rule_id]['item_id']);
                }
            }
        }
        return $products;
    }

    /**
     * Target: All. "Set discount to all products"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_all($filter_items, $items, $target, $rule)
    {
        if (!$filter_items) {
            return array("discount" => 0, "affiliate" => 0);
        }
        return self::calculate_target_discount($target, $items, $rule);
    }

    /**
     * Target: All true. "Set discount to all products that meet conditions"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_all_true($filter_items, $items, $target, $rule)
    {
        return self::target_all($filter_items, $filter_items, $target, $rule);
    }

    /**
     * Target: All false. "Set discount to all products that failed conditions"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_all_false($filter_items, $items, $target, $rule)
    {
        $false_items = array_diff_key($items, $filter_items);
        return self::target_all($false_items, $false_items, $target, $rule);
    }

    /**
     * Target: Category. "Set discount to category"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_cat($filter_items, $items, $target, $rule)
    {
        // Если условие не задано, прерываем обработку
        if (!$filter_items || empty($target->condition)) {
            return array("discount" => 0, "affiliate" => 0);
        }

        $discount_items = self::filter_by_cat($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule);
    }

    /**
     * Target: Category and subcategories. "Set discount to category and subcategories"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_cat_all($filter_items, $items, $target, $rule)
    {
        // Если условие не задано, прерываем обработку
        if (!$filter_items || empty($target->condition)) {
            return array("discount" => 0, "affiliate" => 0);
        }

        $discount_items = self::filter_by_cat_all($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule);
    }

    /**
     * Target: Set. "Set discount to set"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_set($filter_items, $items, $target, $rule)
    {
        // Если условие не задано, прерываем обработку
        if (!$filter_items || empty($target->condition)) {
            return array("discount" => 0, "affiliate" => 0);
        }

        $discount_items = self::filter_by_set($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule);
    }

    /**
     * Target: Type. "Set discount to type"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_type($filter_items, $items, $target, $rule)
    {
        // Если условие не задано, прерываем обработку
        if (!$filter_items || empty($target->condition)) {
            return array("discount" => 0, "affiliate" => 0);
        }

        $discount_items = self::filter_by_type($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule);
    }

    /**
     * Target: Product. "Set discount to product"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_product($filter_items, $items, $target, $rule)
    {
        // Если условие не задано, прерываем обработку
        if (!$filter_items || empty($target->condition)) {
            return array("discount" => 0, "affiliate" => 0);
        }

        $discount_items = self::filter_by_product($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule);
    }

    /**
     * Target: Feature. "Set discount to product feature"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     */
    protected static function target_feature($filter_items, $items, $target, $rule)
    {
        // Если условие не задано, прерываем обработку
        if (!$filter_items || empty($target->condition)) {
            return array("discount" => 0, "affiliate" => 0);
        }

        $discount_items = self::filter_by_feature($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule);
    }

    /**
     * Target: Shipping. "Set discount to shipping method"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @return array
     * @throws waException
     */
    private static function target_shipping($filter_items, $items, $target, $rule)
    {
        if (!$filter_items || empty($target->condition) || waRequest::param('quickorder_ignore_sd')) {
            return array("discount" => 0, "affiliate" => 0);
        }
        $contact_id = (self::$user ? (self::$user->getId() ? self::$user->getId() : 0) : 0);
        if ($contact_id) {
            $contact = self::$user;
        } else {
            $order_params = (new shopFlexdiscountData())->getCurrentOrderParams();
            if (!$contact = $order_params['contact']) {
                $contact = wa()->getUser();
            }
        }

        if (self::filter_by_shipping($filter_items, (array) $target->condition, true)) {
            $order = shopFlexdiscountData::getOrderInfo();
            if (!empty($order['id'])) {
                $shipping = !empty($order['params']['shipping_id']) ? $order['params']['shipping_id'] : 0;
                $rate_id = !empty($order['params']['shipping_rate_id']) ? $order['params']['shipping_rate_id'] : 0;
            } else {
                if (!isset($order_params)) {
                    $order_params = (new shopFlexdiscountData())->getCurrentOrderParams();
                }

                if (wa('shop')->getVersion() >= 8) {
                    $route = wa()->getRouting()->getRoute();
                }
                $shipping = !empty($order_params['shipping']) ? $order_params['shipping']['id'] : 0;
                $rate_id = !empty($order_params['shipping']) ? $order_params['shipping']['rate_id'] : 0;
            }

            if ($shipping) {
                $shipping_class = new shopCheckoutShipping();

                $plugin_info = (new shopPluginModel())->getById($shipping);
                $plugin = shopShipping::getPlugin($plugin_info['plugin'], $shipping);
                $total = $order['total'];
                $currency = $plugin->allowedCurrency();
                $current_currency = $order['currency'];

                // Игнорируем хук frontend_products, чтобы не было рекурсии
                waRequest::setParam('flexdiscount_skip_frontend_products', 1);
                if (!empty($order['id'])) {
                    $order_items = array();
                    $all_items = self::getAllItems(null);
                    foreach ($all_items as $ai) {
                        if ($ai['type'] == 'product') {
                            $order_items[] = $ai['product'];
                        }
                    }
                    $shipping_items = $order_items;
                } else {
                    // Учитываем плагин "Купить в 1 клик" (quickorder)
                    if (waRequest::param('plugin', '') == 'quickorder') {
                        $shipping_cl = new shopQuickorderPluginWaShipping($order_params['quickorder_cart']);
                        $shipping_items = $shipping_cl->getItems();
                    } else {
                        $shipping_items = $shipping_class->getItems($plugin->allowedWeightUnit());
                    }
                }
                // Учитываем плагин "Купить в 1 клик" (quickorder)
                if (waRequest::param('plugin', '') == 'quickorder') {
                    $customer = waRequest::post('customer_' . $shipping);
                    $address = ifset($customer['address.shipping'], $shipping_cl->getAddress());
                    $total = $order_params['quickorder_cart']->getTotal(false);
                    $rates = $shipping_cl->getSingleShippingRates($shipping, $shipping_items, $address, $total, true);
                } else {
                    // SS8. Проверяем, возможно стоимость доставки уже была передана.
                    $cache = new waRuntimeCache('flexdiscount_shipping_price');
                    if ($cache->isCached()) {
                        $rate_id = 'flexdiscount_shipping_price';
                        $flexdiscount_shipping_price = $cache->get();
                        $rates[$rate_id]['rate'] = $flexdiscount_shipping_price['rate'];
                        // Валюта доставки. В версиях SS < 8.5.1 работало без этого
                        $currency = $flexdiscount_shipping_price['currency'];
                    } else {
                        $rates = $plugin->getRates($shipping_items, $shipping_class->getAddress($contact), array('total_price' => $total));
                    }
                }
                waRequest::setParam('flexdiscount_skip_frontend_products', null);

                if ($rates && !is_string($rates)) {
                    if ($rate_id === null) {
                        $rate_id = key($rates);
                    }
                    if (isset($rates[$rate_id])) {
                        $rate = $rates[$rate_id];
                    } else {
                        $rate = array('rate' => 0);
                    }
                    if ($rate['rate']) {
                        if (is_array($rate['rate'])) {
                            $rate['rate'] = max($rate['rate']);
                        }
                        if ($currency != $current_currency) {
                            $rate['rate'] = shop_currency($rate['rate'], $currency, $current_currency, false);
                        }
                        // rounding
                        if ($rate['rate'] && wa()->getSetting('round_shipping')) {
                            $rate['rate'] = shopRounding::roundCurrency($rate['rate'], $current_currency);
                        }
                    }

                    $target->base = $target->affiliate_base = $target->total = $rate['rate'];
                    /* Рассчитываем базу для скидки */
                    if (!empty($rule['discount_shipping_base']) && $rule['discount_shipping_base'] == 'order') {
                        $target->base = $total;
                    }
                    /* Рассчитываем базу для бонусов */
                    if (!empty($rule['affiliate_shipping_base']) && $rule['affiliate_shipping_base'] == 'order') {
                        $target->affiliate_base = $total;
                    }

                    $target->primary_total = shop_currency($target->affiliate_base, $current_currency, wa()->getConfig()->getCurrency(true), false);

                    // SS8 TODO Проверить, как отработает в обычной корзине
                    if (0 && wa('shop')->getVersion() >= 8 && 2 == ifset($route, 'checkout_version', null)) {
                        // Скидка в валюте
                        $rule_discount = !empty($rule['discount_shipping']) ? $rule['discount_shipping'] : (!isset($rule['discount_shipping']) && !empty($rule['discount']) ? $rule['discount'] : 0);
                        $rule_currency = !empty($rule['discount_shipping_currency']) ? $rule['discount_shipping_currency'] : (!isset($rule['discount_shipping_currency']) && !empty($rule['discount_currency']) ? $rule['discount_currency'] : 0);
                        $rule_discount = !empty($rule_discount) && $rule_discount > 0 ? (float) shop_currency($rule_discount, (!empty($rule_currency) ? $rule_currency : wa('shop')->getConfig()->getCurrency(true)), self::getOrderInfo('currency'), false) : 0;
                        // Рассчитываем скидку для способов доставки
                        $shipping_percentage = !empty($rule['discount_shipping_percentage']) ? $rule['discount_shipping_percentage'] : (!isset($rule['discount_shipping_percentage']) && !empty($rule['discount_percentage']) ? $rule['discount_percentage'] : 0);
                        $shipping_discount = (!empty($shipping_percentage) ? max(0.0, min(100.0, shopFlexdiscountHelper::floatVal($shipping_percentage))) * $target->base / 100.0 : 0) + $rule_discount;
                        $cache = new waRuntimeCache('flexdiscount_shipping_discount');
                        $cache->set($shipping_discount);
                    } else {
                        return self::calculate_target_discount($target, $filter_items, $rule);
                    }
                }
            }
        }
        return array("discount" => 0, "affiliate" => 0);
    }

}
