<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountWorkflow extends shopFlexdiscountCore
{

    private static $available_products = array();

    /**
     * Add product to order
     *
     * @param array $product
     * @param string $currency
     * @return array Order params
     * @throws waException
     */
    public function addToOrder($product, $currency = '')
    {
        $params = $this->getOrder();

        $prod = is_object($product) ? $product->getData() : $product;
        if (!isset($prod['product'])) {
            $prod['product'] = $prod;
        }
        $prod['quantity'] = isset($prod['quantity']) ? $prod['quantity'] : 1;

        $merge = false;
        $ignore_service = array();
        // Проверяем есть ли такой товар в корзине, если имеется, то увеличиваем количество
        foreach ($params['order']['items'] as $k => $item) {
            if ($item['sku_id'] == $prod['sku_id']) {
                $params['order']['items'][$k]['quantity'] += $prod['quantity'];
                $merge = true;
                $item_id = $k;
            }
            // Если в заказе имеется услуга и, мы ее же пытаемся добавить с новым товаром, 
            // устанавливаем флаг, чтобы игнорировать повторное добавление услуги
            if ($item['type'] == 'service' && $prod['sku_id'] == $item['sku_id'] && isset($prod['services'][$item['service_variant_id']])) {
                $ignore_service[$item['service_variant_id']] = true;
            }
        }

        $prod['currency'] = $currency ? $currency : $params['order']['currency'];
        if (!$merge) {
            $params['order']['items'][] = $prod;
            end($params['order']['items']);
            $item_id = key($params['order']['items']);
            reset($params['order']['items']);
        }
        // Добавляем услуги к заказу
        if (!empty($prod['services'])) {
            foreach ($prod['services'] as $variant_id => $variant) {
                if (empty($ignore_service[$variant_id])) {
                    $params['order']['items'][] = array(
                        'type' => 'service',
                        'service_id' => $variant['service_id'],
                        'service_variant_id' => $variant_id,
                        'parent_id' => $item_id,
                        'price' => $variant['price'],
                        'currency' => $variant['currency']
                    );
                }
                // Изменяем общую цену заказа
                $params['order']['total'] += (float) $variant['price'] * $prod['quantity'];
            }
        }

        // Добавляем товары в общую выборку при динамическом обновлении информационных блоков
        if (waRequest::isXMLHttpRequest()) {
            $data_class = new shopFlexdiscountData();
            $data_class->setShopProducts($params['order']['items']);
        }

        // Изменяем общую цену заказа
        $product_price = (float) shop_currency((float) $prod['price'] * $prod['quantity'], $prod['currency'], $params['order']['currency'], false);
        $params['order']['total'] += shopRounding::roundCurrency($product_price, $params['order']['currency']);

        return $params;
    }

    /**
     * Get order params
     *
     * @param int $order_id
     * @param bool $skip_shop_cart
     * @return array
     * @throws waException
     */
    public function getOrder($order_id = 0, $skip_shop_cart = false)
    {
        if (!self::$order) {
            // Если необходимо получить информацию и просчитать скидки для конкретного заказа
            if ($order_id) {
                $order_data = (new shopOrderModel())->getOrder($order_id, true);
            } else {
                $shopCart = new shopCart();
            }
            // Получаем содержимое заказа.
            // Для фронтенда - набор из корзины
            // Для готового заказа - массив товаров из БД
            // Для бекенда - пустой массив (скорее всего происходит выгрузка)

            $items = $order_id ? self::prepareOrderItems($order_data['items'], $order_data['currency']) : (wa()->getEnv() == 'frontend' && !$skip_shop_cart ? $shopCart->items(false) : array());
            $contact = $order_id ? new waContact($order_data['contact_id']) : wa()->getUser();
            self::$order = array(
                'order' => array(
                    'currency' => $order_id ? $order_data['currency'] : wa('shop')->getConfig()->getCurrency(false),
                    'items' => $items ? $items : array(),
                    'ids' => array(),
                    'contact' => $contact,
                    'total' => $order_id ? $order_data['total'] : (!$skip_shop_cart ? $shopCart->total(false) : 0),
                ),
                'contact' => $contact,
                'apply' => $order_id ? 1 : 0
            );
            if ($order_id) {
                $order['order']['id'] = $order_id;
            }
        }
        return self::$order;
    }

    /**
     * Get available discounts for product
     *
     * @param array|int|string product
     * @param int $sku_id
     * @param array $filter_by
     * @return array
     */
    public static function getAvailableDiscounts(&$product, $sku_id = 0, $filter_by = array())
    {
        $filter_type = 'discount';

        // Если требуется показать все правила скидок, невзирая на условия
        if ($product === 'all' || $product === null || !$product) {
            $product = self::getAbstractProduct();
            $sku_id = 0;
            $filter_type = 'all';
        }

        $instance = get_class();

        // Получаем обработанные товары
        $cache_work = new waRuntimeCache('flexdiscount_product_workflows');
        $workflows = $cache_work->get();

        // Получаем товар
        if (is_int($product)) {
            $product = new shopProduct($product);
        }
        $product = ($product instanceof shopProduct) ? $product->getData() : $product;
        if (!$product) {
            return array();
        }

        if (!$sku_id) {
            $sku_id = $product['sku_id'];
        } else {
            $find_sku = $sku_id;
        }

        $product = self::prepareProduct($product, isset($find_sku) ? $find_sku : 0);

        // Если товар уже обрабатывался, то возвращаем его данные
        if (isset(self::$available_products[$sku_id])) {
            return self::$available_products[$sku_id];
        } else {
            // Правила скидок
            $discount_groups = shopFlexdiscountHelper::getDiscounts();
            if (!$discount_groups) {
                return array();
            }

            $sku_id = $product['sku_id'];

            // Добавляем товар к заказу
            $order_params = (new shopFlexdiscountWorkflow())->addToOrder($product);

            self::$user = $order_params['contact'];
            self::setOrderInfo($order_params['order']);

            $items = $order_params['order']['items'];
            // Выполняем предварительную обработку товаров
            shopFlexdiscountHelper::workupProducts($items);

            // Получаем информацию о купонах
            $coupon_info = shopFlexdiscountCouponPluginModel::couponCheck();

            // Фильтруем правила запрета
            $deny_rules = self::prepareDenyRules($discount_groups, 'deny');
            $deny_rules = self::getDenyRules($deny_rules, $items);

            // Выполняем перебор групп скидок
            foreach ($discount_groups as $group_id => $group) {
                $rules = $group_id === 0 ? $group : $group['items'];
                foreach ($rules as $rule) {
                    if ($rule['status'] && empty($rule['hide_storefront']) && !$rule['deny']) {

                        // Проверяем наличие купонов у правил скидок
                        if (!empty($rule['enable_coupon']) || !empty($rule['rule_has_coupon'])) {
                            $coupon_not_found = 1;
                            // Если купон не введен, то прерываем обработку правила
                            if (!$coupon_info && !empty($rule['enable_coupon'])) {
                                self::$available_products[$sku_id][$rule['id']] = array();
                                continue;
                            }

                            if ($coupon_info) {
                                // Сохраняем купоны, которые сработали для правила скидок
                                foreach ($coupon_info as $c_id => $c) {
                                    if (isset($c['coupon_rules'][$rule['id']])) {
                                        $coupon_not_found = 0;
                                    }
                                }
                                // Если не сработало ни одного купона, прерываем обработку правила
                                if ($coupon_not_found) {
                                    continue;
                                }
                            }
                        }

                        // Если правило было обработано в действующих скидках, запоминаем его и исключаем из дальнейшей работы
                        if (!empty($workflows[$sku_id]['items'][$rule['id']])) {
                            $r = $workflows[$sku_id]['items'][$rule['id']];
                            self::$available_products[$sku_id][$rule['id']] = array(
                                'rule' => array(
                                    "id" => $rule['id'],
                                    "sort" => $rule['frontend_sort'],
                                    "name" => $r['name'],
                                    "description" => $rule['description'],
                                    "code" => $r['params']['code'],
                                    "discount" => $r['params']['discount'],
                                    "discount_percentage" => $r['params']['discount_percentage'],
                                    "affiliate" => $r['params']['affiliate'],
                                    "affiliate_percentage" => $r['params']['affiliate_percentage']
                                ),
                                'quantity' => $r['quantity'],
                                'discount' => $r['clear_discount'] * $r['quantity'],
                                'affiliate' => $r['affiliate'] * $r['quantity'],
                            );
                            continue;
                        }

                        $all_items = $items;

                        // Проверяем наличие запрещающих правил, которые выбрасывают товары из расчетов
                        if (!empty($deny_rules[$group_id]['drop_from_calc'])) {
                            $all_items = self::dropDenyItems($all_items, $deny_rules[$group_id]['drop_from_calc'], $group_id);
                        }
                        if ($group_id !== 0 && !empty($deny_rules[0]['drop_from_calc'])) {
                            // Применяем общее правило запрета
                            $all_items = self::dropDenyItems($all_items, $deny_rules[0]['drop_from_calc']);
                        }

                        // Проверяем, остался ли товар после запретов
                        if (!$item = self::productInArray($sku_id, $all_items)) {
                            self::$available_products[$sku_id][$rule['id']] = array();
                            continue;
                        }

                        // Цели 
                        $target = self::decode($rule['target']);
                        // Если почему-то целей не оказалось, прерываем обработку этого правила
                        if (!$target) {
                            continue;
                        }

                        // Запоминаем, обрабатывается комплект или нет
                        shopFlexdiscountData::isBundle($rule);

                        // Условия скидок
                        $conditions = self::decode($rule['conditions']);

                        /*
                         * Выполняем анализ целей и фильтрацию условий.
                         *   
                         * Суть: Если у правила скидок имеются условия, то нам необходимо согласно фильтрам
                         * в настройках (фильтры по дате, пользователю и тд) удалить лишние условия для показа доступных скидок. 
                         * 
                         * Важные моменты! 
                         * - Если в целях имеются "Все товары, удовлетворяющие условиям" или "Все товары, не удовлетворяющие условиям",
                         * тогда для них мы должны учитывать и не удалять условия со старшинством 1 (группа условий для Товара).
                         * - Если перечисленных выше целей нет, тогда мы можем игнорировать условия со старшинством 1.
                         * 
                         * Примеры: 
                         * а) Условие - категория 1, цель - категория 2. Если не удалить все условия со старшинством 1, тогда у товаров из
                         * категории 2 не будет блока доступных скидок.
                         * б) Условие - категория 1, цель - Все товары, удовл условиям. Если удалить все условия со старшинством 1, 
                         * тогда у всех товаров будет лишний блок доступных скидок.
                         */

                        // Определяем какие цели используются, чтобы сформировать массивы, содержащие условия со старшинством 1 и не содержащие
                        $conditions_with_precedence = $conditions_without_precedence = null;
                        if (!empty($conditions->conditions)) {
                            // Подготавливаем фильтры условий
                            $filter_by = !empty($rule['available']['status']) ? (!empty($rule['available']['filter_by']) ? $rule['available']['filter_by'] : array()) : $filter_by;
                            if ($filter_by) {
                                if (in_array('user_group', $filter_by)) {
                                    $filter_by[] = 'user';
                                    $filter_by[] = 'ucat';
                                    $filter_by[] = 'user_date';
                                    $filter_by[] = 'user_data';
                                    $filter_by[] = 'user_birthday';
                                }
                                if (in_array('date_group', $filter_by)) {
                                    $filter_by[] = 'date';
                                    $filter_by[] = 'week';
                                    $filter_by[] = 'time';
                                }
                                if (in_array('properties_group', $filter_by)) {
                                    $filter_by = array_merge($filter_by, array('product_name', 'product_sku', 'product_sku_name', 'product_summary', 'product_mt', 'product_mk', 'product_md', 'product_description',
                                        'product_create', 'product_edit', 'product_video', 'product_image', 'product_rating', 'product_rating_count',
                                        'product_price', 'product_margin', 'product_margin_comp', 'product_margin_perc', 'product_margin_comp_perc', 'product_compare_price',
                                        'product_purchase_price', 'product_min_price', 'product_margin_trade_perc', 'product_margin_comp_trade_perc',
                                        'product_max_price', 'product_stock', 'product_stock_total', 'product_total_sales', 'product_services', 'product_tags'));
                                }
                                if (in_array('numsum_group', $filter_by)) {
                                    $filter_by['numsum_group'] = array('num_cat' => 1, 'num_cat_all' => 1, 'num_set' => 1, 'num_type' => 1, 'num_all_cat' => 2,
                                        'num_all_cat_all' => 2, 'num_all_set' => 2, 'num_all_type' => 2, 'sum_cat' => 2, 'sum_cat_all' => 2, 'num_feat' => 3,
                                        'sum_feat' => 3);
                                }
                            }
                            foreach ($target as $t) {
                                // Игнорируем цели по доставке
                                if ($t->target == 'shipping' && empty($rule['prod_meet_cond'])) {
                                    continue;
                                }
                                if ($conditions_with_precedence === null && (in_array($t->target, array('all', 'all_true', 'all_false')) || !empty($rule['prod_meet_cond']))) {
                                    $conditions_with_precedence = clone $conditions;
                                    $conditions_with_precedence->conditions = self::filter_conditions($conditions->conditions, $filter_by, $filter_type);
                                }
                                if ($conditions_without_precedence === null && !in_array($t->target, array('all', 'all_true', 'all_false'))) {
                                    $conditions_without_precedence = clone $conditions;
                                    $conditions_without_precedence->conditions = self::filter_conditions($conditions->conditions, $filter_by, $filter_type, true);
                                }
                                // Когда все нужные условия собраны, прерываем перебор целей
                                if ($conditions_with_precedence !== null && $conditions_without_precedence !== null) {
                                    break;
                                }
                            }
                        }

                        self::getAllItems($all_items);

                        // Товары, удовлетворяющие условиям
                        $filter_items_with_precedence = $conditions_with_precedence ? self::filter_items($all_items, $conditions_with_precedence->group_op, $conditions_with_precedence->conditions, 0, 1) : $all_items;
                        $filter_items_without_precedence = $conditions_without_precedence ? self::filter_items($all_items, $conditions_without_precedence->group_op, $conditions_without_precedence->conditions, 0, 1) : $all_items;

                        // Проверяем наличие запрещающих правил
                        if (!empty($deny_rules[$group_id])) {
                            $all_items = self::dropDenyItems($all_items, $deny_rules[$group_id]['all'], $group_id);
                            if ($conditions_with_precedence) {
                                $filter_items_with_precedence = self::dropDenyItems($filter_items_with_precedence, $deny_rules[$group_id]['all'], $group_id);
                            }
                            if ($conditions_without_precedence) {
                                $filter_items_without_precedence = self::dropDenyItems($filter_items_without_precedence, $deny_rules[$group_id]['all'], $group_id);
                            }
                        }
                        if ($group_id !== 0 && !empty($deny_rules[0])) {
                            // Применяем общее правило запрета
                            $all_items = self::dropDenyItems($all_items, $deny_rules[0]['all']);
                            if ($conditions_with_precedence) {
                                $filter_items_with_precedence = self::dropDenyItems($filter_items_with_precedence, $deny_rules[0]['all']);
                            }
                            if ($conditions_without_precedence) {
                                $filter_items_without_precedence = self::dropDenyItems($filter_items_without_precedence, $deny_rules[0]['all']);
                            }
                        }

                        // Назначаем скидки на выбранные объекты
                        foreach ($target as $t) {
                            if ($t->target == 'shipping' && empty($rule['show_shipping'])) {
                                continue;
                            }
                            $function_name = 'avail_target_' . $t->target;
                            if (method_exists($instance, $function_name)) {
                                // Общее значение скидки и бонусов для правила
                                $result = self::$function_name((in_array($t->target, array('all', 'all_true', 'all_false')) || !empty($rule['prod_meet_cond'])) ? $filter_items_with_precedence : $filter_items_without_precedence, $all_items, $t, $rule, $product['sku_id']);
                                if ($result === false) {
                                    continue;
                                }
                            }
                        }

                        // Опция "Отображать доступные скидки для товаров, которые удовлетворяют условиям"
                        if (!empty($rule['prod_meet_cond']) && empty(self::$available_products[$sku_id][$rule['id']]) && self::productInArray($sku_id, $filter_items_with_precedence)) {
                            self::initAvailableProduct($rule, $sku_id, array('prod_meet_cond' => 1));
                        }

                        // Ограничение для правила
                        self::limitAvailableDiscountValue($rule, $item);
                    }
                }
            }
        }
        return isset(self::$available_products[$sku_id]) ? self::$available_products[$sku_id] : array();
    }

    /**
     * Get all deny rules for product
     *
     * @param array|int $product
     * @param int $sku_id
     * @return array
     */
    public static function getAllDenyRules($product, $sku_id = 0)
    {
        // Если требуется показать все правила скидок, невзирая на условия
        if ($product === null || !$product) {
            $product = self::getAbstractProduct();
            $sku_id = 0;
        }

        $instance = get_class();

        // Получаем товар
        if (is_int($product)) {
            $product = new shopProduct($product);
        }
        $product = ($product instanceof shopProduct) ? $product->getData() : $product;
        if (!$product) {
            return array();
        }

        // Правила скидок
        $discount_groups = shopFlexdiscountHelper::getDiscounts();
        if (!$discount_groups) {
            return array();
        }

        $product = self::prepareProduct($product, $sku_id);
        $sku_id = $product['sku_id'];

        // Добавляем товар к заказу
        $order_params = (new shopFlexdiscountWorkflow())->addToOrder($product);

        self::$user = $order_params['contact'];
        self::setOrderInfo($order_params['order']);

        $items = $order_params['order']['items'];
        // Выполняем предварительную обработку товаров
        shopFlexdiscountHelper::workupProducts($items);
        self::getAllItems($items);

        $deny_rules = array();
        // Фильтруем правила запрета
        foreach ($discount_groups as $group_id => $group) {
            $rules = $group_id === 0 ? $group : $group['items'];
            foreach ($rules as $rule) {
                if ($rule['deny'] && $rule['status']) {
                    // Условия
                    $conditions = self::decode($rule['conditions']);

                    // Товары, удовлетворяющие условиям
                    $result_items = $conditions ? self::filter_items($items, $conditions->group_op, $conditions->conditions) : $items;
                    if ($result_items) {
                        $target = self::decode($rule['target']);
                        foreach ($target as $t) {
                            $function_name = 'avail_target_' . $t->target;
                            if (method_exists($instance, $function_name)) {
                                // Товары, на которые скидка не распространяется
                                $deny_items = self::$function_name($result_items, $items, $t, $rule, $sku_id);
                                if ($deny_items || !$sku_id) {
                                    $deny_rules[$rule['id']] = $rule;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $deny_rules;
    }

    /**
     * Filter deny rules by precedence 2
     *
     * @param array $groups
     * @param string $type
     * @return array
     */
    private static function prepareDenyRules($groups, $type)
    {
        $settings = shopFlexdiscountHelper::getSettings();
        // Получаем условия, которые необходимо игнорировать в правилах запрета при показе доступных скидок
        $filter_by = !empty($settings['flexdiscount_avail_discounts']['ignore_deny']) ? $settings['flexdiscount_avail_discounts']['ignore_deny'] : array();
        foreach ($groups as $group_id => $group) {
            $rules = $group_id === 0 ? $group : $group['items'];
            foreach ($rules as $k => $rule) {
                if ($rule['deny'] && $rule['status']) {
                    // Условия
                    $rules[$k]['conditions'] = self::decode($rule['conditions']);
                    // Удаляем все условия со определенным старшинством в зависимости от типа скидки
                    if ($rules[$k]['conditions']) {
                        $filter_by = !empty($rules[$k]['available']['status']) ? (!empty($rules[$k]['available']['ignore_deny']) ? $rules[$k]['available']['ignore_deny'] : array()) : $filter_by;
                        $rules[$k]['conditions']->conditions = self::filter_conditions($rules[$k]['conditions']->conditions, $filter_by, $type);
                        // Если правило запрета осталось без условий, удаляем его, чтобы не оказалось так, что все товары попали под запрет
                        if (empty($rules[$k]['conditions']->conditions)) {
                            unset($rules[$k]);
                        }
                    }
                }
            }
            if (!$rules) {
                unset($groups[$group_id]);
                continue;
            }
            if ($group_id === 0) {
                $groups[$group_id] = $rules;
            } else {
                $groups[$group_id]['items'] = $rules;
            }
        }
        return $groups;
    }

    /**
     * Filter conditions
     *
     * @param array $conditions
     * @param array $filter_by - filter by types
     * @param string $type - discount, or all
     * @param bool $ignore_precedence - should we ignore precedence 1?
     * @return array
     */
    private static function filter_conditions($conditions, $filter_by = array(), $type = 'discount', $ignore_precedence = false)
    {
        // Бывают случаи, когда $conditions является объектом, а не массивом объектов. Исправляем
        if (is_object($conditions)) {
            $new_conditions = array();
            foreach ($conditions as $k => $c) {
                $new_conditions[$k] = $c;
            }
            $conditions = $new_conditions;
        }
        foreach ($conditions as $k => $c) {
            if (isset($c->group_op)) {
                $conditions[$k]->conditions = self::filter_conditions(self::decode($c->conditions), $filter_by, $type);
                if (!$conditions[$k]->conditions) {
                    if ($type == 'discount') {
                        $conditions[$k] = array("op" => "gte", "value" => "0", "type" => "num");
                    } else {
                        unset($conditions[$k]);
                    }
                }
            } else {
                $c = (object) $c;
                if (isset(self::$type_precedence[$c->type])) {
                    $filter_by[] = 'storefront';
                    // Для скидок удаляем все правила согласно фильтру. 
                    // Заменяем некоторые правила для корректной фильтрации
                    if ($type == 'discount' && !in_array($c->type, $filter_by)) {
                        //self::$type_precedence[$c->type] !== 1
                        // Для комплектов заменяем условия
                        if (shopFlexdiscountData::isBundle()) {
                            if ($c->type == 'num_prod') {
                                $conditions[$k] = array("op" => "eq", "product_type" => $c->product_type, "value" => $c->field, "type" => "product");
                                continue;
                            } elseif ($c->type == 'num_all_cat' || $c->type == 'num_all_cat_all') {
                                $conditions[$k] = array("op" => $c->op, "value" => $c->field, "type" => ($c->type == 'num_all_cat' ? 'cat' : 'cat_all'));
                                continue;
                            } elseif ($c->type == 'num_all_set' || $c->type == 'num_all_type') {
                                $conditions[$k] = array("op" => $c->op, "value" => $c->field, "type" => ($c->type == 'num_all_set' ? 'set' : 'type'));
                                continue;
                            } elseif ($c->type == 'num_feat') {
                                $conditions[$k] = array("op" => $c->op, "field" => $c->field, "value" => $c->ext, "type" => 'feature');
                                continue;
                            }
                        }
                        // Заменяем условия, чтобы учитывать колич-е и суммарные значения товаров из категорий/списков/типов/хар-к
                        if (isset($filter_by['numsum_group'][$c->type])) {
                            switch ($filter_by['numsum_group'][$c->type]) {
                                // num_cat, num_cat_all, num_set, num_type
                                case 1:
                                    $conditions[$k] = array("op" => $c->op, "value" => $c->ext, "type" => $c->type);
                                    break;
                                // num_all_cat, num_all_cat_all, num_all_set, num_all_type, sum_cat, sum_cat_all
                                case 2:
                                    $conditions[$k] = array("op" => $c->op, "value" => $c->field, "type" => $c->type);
                                    break;
                                // num_feat, sum_feat
                                case 3:
                                    $conditions[$k] = array("op" => $c->op, "field" => $c->field, "value" => $c->ext, "type" => $c->type);
                                    break;
                            }
                        } // Если условие со старшинством 1 не нужно игнорировать, тогда идем дальше
                        elseif (!$ignore_precedence && self::$type_precedence[$c->type] === 1) {
                            continue;
                        } // Удаляем условие
                        else {
                            $conditions[$k] = array("op" => "gte", "value" => "0", "type" => "num");
                        }
                    } // Удаляем условия согласно фильтру в правилах запрета для отображения доступных скидок
                    elseif ($type == 'deny' && self::$type_precedence[$c->type] !== 0) {
                        $precedence_filter = array(1 => 'product_group', 2 => 'properties_group', 3 => 'cart_group');
                        if (!empty($precedence_filter[self::$type_precedence[$c->type]]) && in_array($precedence_filter[self::$type_precedence[$c->type]], $filter_by)) {
                            unset($conditions[$k]);
                        }
                    } // Если выводим все скидки, то учитываем только фильтр
                    elseif ($type == 'all' && !empty($filter_by) && !in_array($c->type, $filter_by)) {
                        $conditions[$k] = array("op" => "gte", "value" => "0", "type" => "num");
                    }
                }
            }
        }
        return $conditions;
    }

    /**
     * Calculate target discount
     *
     * @param array $target
     * @param array $discount_items
     * @param array $rule
     * @param int $sku_id
     * @return array|bool Discount and affiliate
     */
    private static function calculate_target_discount($target, $discount_items, $rule, $sku_id)
    {
        if (!$item = self::productInArray($sku_id, $discount_items)) {
            if (!isset(self::$available_products[$sku_id][$rule['id']])) {
                if (!$sku_id) {
                    if (!empty($rule['deny'])) {
                        return true;
                    }
                    // Инициализируем скидки для абстрактного товара
                    self::initAvailableProduct($rule, $sku_id);
                } else {
                    self::$available_products[$sku_id][$rule['id']] = array();
                }
            }
            return false;
        }

        if (!empty($rule['deny'])) {
            return true;
        }

        $primary_currency = wa('shop')->getConfig()->getCurrency(true);
        // Скидка в валюте
        $rule['discount'] = !empty($rule['discount']) && $rule['discount'] > 0 ? (float) shop_currency($rule['discount'], (!empty($rule['discount_currency']) ? $rule['discount_currency'] : $primary_currency), self::getOrderInfo('currency'), false) : 0;
        // Бонусы
        $rule['affiliate'] = !empty($rule['affiliate']) && $rule['affiliate'] > 0 ? shopFlexdiscountHelper::floatVal($rule['affiliate']) : 0;

        // Размер скидки товара
        $product_discount = !empty($rule['discount_percentage']) ? max(0.0, min(100.0, shopFlexdiscountHelper::floatVal($rule['discount_percentage']))) * self::getRuleBase($item, $rule) / 100.0 : 0;
        // Размер бонусов, начисленных на товар
        $affiliate_value = self::getRuleBase($item, $rule, 'affiliate_base');
        $product_affiliate = !empty($rule['affiliate_percentage']) ? round(max(0.0, min(100.0, (float) $rule['affiliate_percentage'])) * (float) shop_currency($affiliate_value, self::getOrderInfo('currency'), $primary_currency, false) / 100.0, 2) : 0;

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
        if (!$product_discount && !$product_affiliate && isset($item['id']) && $item['id'] !== 0) {
            if (!isset(self::$available_products[$sku_id][$rule['id']])) {
                self::$available_products[$sku_id][$rule['id']] = array();
            }
            return false;
        }

        // Цена товара с учетом количества
        $full_product_price = $item['price'] * $item['quantity'];

        // Цена со скидкой. Вычисляется исходя из формулы.
        $price_with_discount = self::getRuleBase($item, $rule, 'discount_base_main') * $item['quantity'] - $product_discount;
        // Если значение цены со скидкой больше, чем цена товара, тогда скидка равна 0
        if ($price_with_discount > $full_product_price) {
            if (!isset(self::$available_products[$sku_id][$rule['id']])) {
                self::$available_products[$sku_id][$rule['id']] = array();
            }
            return false;
        }
        $product_discount = $full_product_price - $price_with_discount;

        // Не даем скидку больше, чем цена товара
        if ($product_discount > $full_product_price) {
            $product_discount = $full_product_price;
        }

        // Инициализируем товар, участвующий в скидках и получивший бонусы
        self::initAvailableProduct($rule, $item['sku_id'], (array) $target);

        // Запоминаем размер скидки для каждого товара и кол-во товаров, участвующих в скидках
        self::$available_products[$item['sku_id']][$rule['id']]['quantity'] += (float) $item['quantity'];
        self::$available_products[$item['sku_id']][$rule['id']]['discount'] += (float) $product_discount;
        self::$available_products[$item['sku_id']][$rule['id']]['affiliate'] += (float) $product_affiliate;

        return $item;
    }

    /**
     * Init discount and affiliate product
     *
     * @param array $rule
     * @param int $sku_id
     * @param array $params
     */
    private static function initAvailableProduct($rule, $sku_id, $params = array())
    {
        if (!isset(self::$available_products[$sku_id][$rule['id']]) || (isset(self::$available_products[$sku_id][$rule['id']]) && !isset(self::$available_products[$sku_id][$rule['id']]['quantity']))) {
            self::$available_products[$sku_id][$rule['id']] = array(
                'quantity' => 0,
                'discount' => 0,
                'affiliate' => 0,
            );
            // Если у данного товара не было скидок, и мы были вынуждены проверить его из-за опции 
            // "Отображать доступные скидки для товаров, которые удовлетворяют условиям", ставим флаг, что для данного товара можно не просчитывать
            // скидки. Вывод скидок следует регулировать в типах отображения
            if (!empty($params['prod_meet_cond'])) {
                self::$available_products[$sku_id][$rule['id']]['without_discount'] = 1;
            }
        }
        self::$available_products[$sku_id][$rule['id']]['rule'] = array(
            "id" => $rule['id'],
            "sort" => $rule['frontend_sort'],
            "name" => $rule['name'] ? $rule['name'] : _wp("Discount #") . $rule['id'],
            "description" => $rule['description'] ? $rule['description'] : '',
            "code" => $rule['code'],
            "discount" => !empty($rule['discount']) ? shop_currency($rule['discount'], !empty($rule['discount_currency']) ? $rule['discount_currency'] : wa('shop')->getConfig()->getCurrency(true), wa('shop')->getConfig()->getCurrency(false), false) : 0,
            "discount_percentage" => !empty($rule['discount_percentage']) ? $rule['discount_percentage'] : 0,
            "affiliate" => !empty($rule['affiliate']) ? $rule['affiliate'] : 0,
            "affiliate_percentage" => !empty($rule['affiliate_percentage']) ? $rule['affiliate_percentage'] : 0
        );
    }

    /**
     * Limit available discount value
     *
     * @param array $rule
     * @param array $item
     *
     */
    private static function limitAvailableDiscountValue($rule, $item)
    {
        // Ограничение скидки для товара
        if (!empty($rule['limit']['status']) && !empty($rule['limit']['value']) && $rule['limit']['value'] >= 0) {
            $limit = $rule['limit'];
            // Собираем минимальное значение цены товара
            $product_price_minimum = 0;
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

            // Если товар участвовал в скидках
            if (!empty(self::$available_products[$item['sku_id']][$rule['id']])) {
                // Размер скидки для одной единицы товара
                $product_discount = self::$available_products[$item['sku_id']][$rule['id']]['discount'] / self::$available_products[$item['sku_id']][$rule['id']]['quantity'];
                // Если размер скидки для товара больше, чем установлено ограничением
                if ($item['price'] - $product_discount < $product_price_minimum) {
                    // Меняем скидку для товара
                    self::$available_products[$item['sku_id']][$rule['id']]['discount'] = shopFlexdiscountHelper::round($item['price'] - $product_price_minimum) * self::$available_products[$item['sku_id']][$rule['id']]['quantity'];
                    // Если скидка товара ниже 0, это означает, что минимальная цена товара получилась выше самой цены товара.
                    // Удаляем товар из массива скидок
                    if (self::$available_products[$item['sku_id']][$rule['id']]['discount'] <= 0) {
                        unset(self::$available_products[$item['sku_id']][$rule['id']]);
                    }
                }
            }
        }

        // Максимальное значение бонусов для товара
        $maximum_product_affiliate = (isset($rule['maximum_product_affiliate']) && $rule['maximum_product_affiliate'] !== '') ? shopFlexdiscountHelper::floatVal($rule['maximum_product_affiliate']) : null;
        if ($maximum_product_affiliate !== null && !empty(self::$available_products[$item['sku_id']][$rule['id']])) {
            // Размер бонусов для одной единицы товара
            $product_affiliate = self::$available_products[$item['sku_id']][$rule['id']]['affiliate'] / self::$available_products[$item['sku_id']][$rule['id']]['quantity'];
            // Если размер бонусов для товара больше, чем установлено ограничением
            if ($product_affiliate > $maximum_product_affiliate) {
                // Меняем бонусы для товара
                self::$available_products[$item['sku_id']][$rule['id']]['affiliate'] = $maximum_product_affiliate * self::$available_products[$item['sku_id']][$rule['id']]['quantity'];
            }
        }
    }

    /**
     * Target: All. "Set discount to all products"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_all($filter_items, $items, $target, $rule, $sku_id)
    {
        if (!$filter_items) {
            if (!isset(self::$available_products[$sku_id][$rule['id']])) {
                self::$available_products[$sku_id][$rule['id']] = array();
            }
            return false;
        }
        return self::calculate_target_discount($target, $items, $rule, $sku_id);
    }

    /**
     * Target: All true. "Set discount to all products that meet conditions"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return bool
     */
    protected static function avail_target_all_true($filter_items, $items, $target, $rule, $sku_id)
    {
        return self::avail_target_all($filter_items, $filter_items, $target, $rule, $sku_id);
    }

    /**
     * Target: All false. "Set discount to all products that failed conditions"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return bool
     */
    protected static function avail_target_all_false($filter_items, $items, $target, $rule, $sku_id)
    {
        $false_items = array_diff_key($items, $filter_items);
        return self::avail_target_all($false_items, $false_items, $target, $rule, $sku_id);
    }

    /**
     * Target: Category. "Set discount to category"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_cat($filter_items, $items, $target, $rule, $sku_id)
    {
        // Если условие не задано, прерываем обработку
        if (empty($target->condition) || !$filter_items) {
            return false;
        }

        $discount_items = self::filter_by_cat($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule, $sku_id);
    }

    /**
     * Target: Category and subcategories. "Set discount to category and subcategories"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_cat_all($filter_items, $items, $target, $rule, $sku_id)
    {
// Если условие не задано, прерываем обработку  
        if (empty($target->condition) || !$filter_items) {
            return false;
        }

        $discount_items = self::filter_by_cat_all($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule, $sku_id);
    }

    /**
     * Target: Set. "Set discount to set"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_set($filter_items, $items, $target, $rule, $sku_id)
    {
// Если условие не задано, прерываем обработку
        if (empty($target->condition) || !$filter_items) {
            return false;
        }

        $discount_items = self::filter_by_set($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule, $sku_id);
    }

    /**
     * Target: Type. "Set discount to type"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_type($filter_items, $items, $target, $rule, $sku_id)
    {
        // Если условие не задано, прерываем обработку
        if (empty($target->condition) || !$filter_items) {
            return false;
        }

        $discount_items = self::filter_by_type($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule, $sku_id);
    }

    /**
     * Target: Product. "Set discount to product"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_product($filter_items, $items, $target, $rule, $sku_id)
    {
        // Если условие не задано, прерываем обработку
        if (empty($target->condition) || !$filter_items) {
            return false;
        }

        $discount_items = self::filter_by_product($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule, $sku_id);
    }

    /**
     * Target: Feature. "Set discount to product feature"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_feature($filter_items, $items, $target, $rule, $sku_id)
    {
// Если условие не задано, прерываем обработку
        if (empty($target->condition) || !$filter_items) {
            return false;
        }

        $discount_items = self::filter_by_feature($items, (array) $target->condition);

        return self::calculate_target_discount($target, $discount_items, $rule, $sku_id);
    }

    /**
     * Target: Product. "Set discount to shipping"
     *
     * @param array $filter_items
     * @param array $items
     * @param array $target
     * @param array $rule
     * @param int $sku_id
     * @return array|bool
     */
    protected static function avail_target_shipping($filter_items, $items, $target, $rule, $sku_id)
    {
        if (!$filter_items || ($filter_items && !self::productInArray($sku_id, $filter_items))) {
            return false;
        }
        // Инициализируем скидки для абстрактного товара
        self::initAvailableProduct($rule, $sku_id);
        return false;
    }

    /**
     * Check, if product ID in array of cart items
     *
     * @param int $sku_id
     * @param array $items
     * @return boolean|array
     */
    private static function productInArray($sku_id, $items)
    {
        foreach ($items as $it) {
            if ($it['sku_id'] == $sku_id) {
                return $it;
            }
        }
        return false;
    }

    /**
     * Prepare product. Convert prices
     *
     * @param array $product
     * @param int $sku_id
     * @return array
     */
    public static function prepareProduct($product, $sku_id = 0)
    {
        // Текущая валюта
        $current_cur = wa('shop')->getConfig()->getCurrency(false);
        // Основная валюта
        $primary_cur = wa('shop')->getConfig()->getCurrency(true);

        if ($sku_id) {
            $sku = (new shopProductSkusModel())->getById($sku_id);
            // Если переданные данные совпадают
            if ($sku && $sku['product_id'] == $product['id']) {
                $product_currency = !empty($product['unconverted_currency']) ? $product['unconverted_currency'] : $product['currency'];
                $currency_rounding = shopFlexdiscountHelper::getSettings('currency_rounding');

                // Вызываем хук frontend_products
                $skus = array($sku['id'] => $sku);
                $event_params = array("skus" => &$skus);
                wa('shop')->event('frontend_products', $event_params);
                $sku = reset($skus);
                $sku['product'] = $product;
                $sku['sku_id'] = $sku['id'];
                $sku['id'] = $sku['product_id'];
                $sku['quantity'] = !empty($product['quantity']) ? $product['quantity'] : 1;
                // Переводим цены товара в текущую валюту
                $sku['price'] = isset($sku['old_price']) ? $sku['old_price'] : $sku['price'];
                $sku['price'] = shop_currency($sku['price'], $product_currency, $current_cur, false);
                $sku['compare_price'] = isset($sku['old_compare_price']) ? $sku['old_compare_price'] : $sku['compare_price'];
                $sku['compare_price'] = shop_currency($sku['compare_price'], $product_currency, $current_cur, false);
                $sku['purchase_price'] = shop_currency($sku['purchase_price'], $product_currency, $current_cur, false);
                if (!empty($currency_rounding)) {
                    $sku['price'] = shopRounding::roundCurrency($sku['price'], $current_cur);
                    $sku['compare_price'] = shopRounding::roundCurrency($sku['compare_price'], $current_cur);
                    $sku['purchase_price'] = shopRounding::roundCurrency($sku['purchase_price'], $current_cur);
                }
                if ($sku['compare_price'] == $sku['price']) {
                    $sku['compare_price'] = 0;
                }
                // Сохраняем услуги, если они имеются
                if (!empty($product['services'])) {
                    $sku['services'] = $product['services'];
                }
                $product = $sku;
            }
        } else {
            // Переводим цены товара в текущую валюту
            $product['price'] = isset($product['old_price']) ? $product['old_price'] : $product['price'];
            $product['price'] = shop_currency($product['price'], isset($product['old_price']) ? $current_cur : $primary_cur, $current_cur, false);

            $product['compare_price'] = isset($product['old_compare_price']) ? $product['old_compare_price'] : $product['compare_price'];
            $product['compare_price'] = shop_currency($product['compare_price'], isset($product['old_compare_price']) ? $current_cur : $primary_cur, $current_cur, false);
            if ($product['compare_price'] == $product['price']) {
                $product['compare_price'] = 0;
            }
            if (isset($product['purchase_price'])) {
                $product['purchase_price'] = shop_currency($product['purchase_price'], $primary_cur, $current_cur, false);
            }
            if (!isset($product['unconverted_currency'])) {
                $product['unconverted_currency'] = $product['currency'];
            }
        }

        $product['type'] = 'product';
        $product['currency'] = $current_cur;

        return $product;
    }

    private static function prepareOrderItems($items, $currency)
    {
        $order_data = array();
        $k = 0;

        foreach ($items as $item) {
            $item['type'] = 'product';
            $item['currency'] = $currency;
            $item['product_id'] = $item['item']['product_id'];
            $item['quantity'] = $item['item']['quantity'];
            if (!isset($item['purchase_price'])) {
                $item['purchase_price'] = $item['item']['purchase_price'];
            }
            $parent_id = $k;
            $order_data[$k] = $item;
            if (!empty($item['services'])) {
                foreach ($item['services'] as $is) {
                    if (!empty($is['item'])) {
                        $k++;
                        $is['item']['parent_id'] = $parent_id;
                        $is['item']['currency'] = $item['currency'];
                        $order_data[$k] = $is['item'];
                    }
                }
            }
            $k++;
        }
        return $order_data;
    }

}
