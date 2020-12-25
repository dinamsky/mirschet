<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 4.0.0
 * @copyright Serge Rodovnichenko, 2018
 * @license Webasyst
 */

use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Inflector;
use Webit\Util\EvalMath\EvalMath;

/**
 * Class shopSdekintPluginSyrnikShippingProcessor
 */
class shopSdekintPluginSyrnikShippingProcessor
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /** @var shopSdekintPluginCalcRulesModel */
    protected $Rule;

    /**
     * shopSdekintPluginSyrnikShippingProcessor constructor.
     * @param shopSdekintPlugin|null $plugin
     * @param shopSdekintPluginCalcRulesModel|null $model
     * @throws waException
     */
    public function __construct(shopSdekintPlugin $plugin = null, shopSdekintPluginCalcRulesModel $model = null)
    {
        $this->plugin = $plugin ?: wa('shop')->getPlugin('sdekint');
        $this->Rule = $model ?: new shopSdekintPluginCalcRulesModel();
    }

    /**
     * @param array $data
     * @param string $event
     */
    public function process(array &$data, $event)
    {
        $method = Inflector::variable($event) . 'RuleProcessor';
        if (!method_exists($this, $method)) {
            return;
        }
        foreach ($this->Rule->queryAllActive() as $row) {
            $row = $this->Rule->decodeRow($row);
            $this->$method($data, $row);
        }
    }

    /**
     * @param array $data
     * @param array $rule
     */
    protected function beforeCalculateRuleProcessor(array &$data, array $rule)
    {
        if (!$this->isApplicableRule($data['key'], $rule['methods'])) {
            return;
        }

        if (!$this->isConditionsValid($data, $rule['conditions'], $rule['condition_join_type'])) {
            return;
        }

        // На этом этапе мы можем только запрещать-разрешать типы
        if (!$rule['courier']['disabled'] && !$rule['point']['disabled']) {
            return;
        }

        if (ifset($data, 'params', 'delivery_methods', null) && is_array($data['params']['delivery_methods'])) {
            $methods = $data['params']['delivery_methods'];
            foreach ($methods as $k => $m) {
                if (($m == 'to_door') && $rule['courier']['disabled']) {
                    unset($data['params']['delivery_methods'][$k]);
                }
                if (($m == 'to_sklad') && $rule['point']['disabled']) {
                    unset($data['params']['delivery_methods'][$k]);
                }
            }
        }
    }

    /**
     * @param array $data
     * @param array $rule
     */
    protected function afterCalculateRuleProcessor(array &$data, array $rule)
    {
        if (!$this->isApplicableRule($data['key'], $rule['methods'])) {
            return;
        }

        if (!$this->isConditionsValid($data, $rule['conditions'], $rule['condition_join_type'])) {
            return;
        }

        $last_point_price = null;
        $cached_point_price = null;
        $rates = (array)ifset($data, 'rates', []);

        foreach ($rates as $key => $rate) {
            $rule_key = substr_compare($key, 'to_door_', 0, 8, true) === 0 ? 'courier' : 'point';

            if ($rule[$rule_key]['disabled']) {
                unset($data['rates'][$key]);
                continue;
            }

            $price = ifset($rate, 'rate', null);
            if ($price === null) {
                continue;
            }

            if ($rule[$rule_key]['type'] === 'asis') {
                continue;
            }

            $price = $this->plugin->helper->toFloat($price);
            if (($rule_key === 'point') && ($last_point_price !== null) && ($cached_point_price !== null) && ($price == $last_point_price)) {
                $data['rates'][$key]['rate'] = $cached_point_price;
                continue;
            }

            $value = null;
            switch ($rule[$rule_key]['type']) {
                case 'fixed' :
                    $value = $this->plugin->helper->toFloat($rule[$rule_key]['setting']['value']);
                    break;

                case 'grid' :
                    $value = $this->calcPriceByGrid($rule[$rule_key]['setting']['unit'], $rule[$rule_key]['setting']['grid'], $data['items']);
                    break;

                case 'formula' :
                    $formula = str_replace(',', '.', trim($rule[$rule_key]['setting']['value']));
                    if (!$formula) {
                        break;
                    }
                    $calc = new EvalMath();
                    $calc->evaluate('t=' . sprintf('%0.2F', $this->totalItemsPrice($data['items'])));
                    $calc->evaluate('s=' . sprintf('%0.2F', $price));
                    $formula = strtolower($formula);
                    $value = $calc->evaluate($formula);
                    break;
            }

            if ($value !== null) {
                $value = round(max(0, $value), 2);
                if ($rule_key === 'point') {
                    $last_point_price = $price;
                    $cached_point_price = $value;
                }

                $data['rates'][$key]['rate'] = $value;
            }
        }
    }

    /**
     * Подходит ли это правило для конкретного метода доставки?
     *
     * @param int|string $id ID метода доставки
     * @param array|string $methods Методы доставки из правила или строчка 'all'
     * @return bool
     */
    protected function isApplicableRule($id, $methods)
    {
        if (is_string($methods) && $methods === 'all') {
            return true;
        }

        return is_array($methods) && in_array($id, $methods);
    }

    /**
     * Выполняются ли условия из указанного правила?
     *
     * @param array $data данные события
     * @param array $conditions список условий
     * @param string $type тип сравнения 'and' или 'or'
     * @return bool
     */
    protected function isConditionsValid(array $data, array $conditions, $type)
    {
        foreach ($conditions as $condition) {
            $result = false;
            $method = Inflector::variable($condition['type']) . 'Condition';
            if (method_exists($this, $method)) {
                $result = $this->$method($condition, $data);
            }

            if (($type === 'and') && !$result) {
                return false;
            }

            if (($type === 'or') && $result) {
                return true;
            }
        }

        return $type === 'and' ? true : false;
    }

    /**
     * @param array $condition
     * @param array $data
     * @return bool
     */
    protected function citySdekCondition(array $condition, array $data)
    {
        $city_id = ifempty($data, 'params', 'sdek_city', 'city_id', '');
        $result = ($condition['value'] == $city_id);

        return $condition['comparison'] == 'eq' ? $result : !$result;
    }

    /**
     * @param array $condition
     * @param array $data
     * @return bool
     */
    protected function countryCondition(array $condition, array $data)
    {
        $country = ifempty($data, 'address', 'country', '');
        $result = ($condition['value'] == $country);

        return $condition['comparison'] == 'eq' ? $result : !$result;
    }

    /**
     * @param array $condition
     * @param array $data
     * @return bool
     */
    protected function regionCondition(array $condition, array $data)
    {
        $country = ifempty($data, 'address', 'country', '');
        $region = ifempty($data, 'address', 'region', '');

        $result = (($condition['value']['country'] == $country) && ($condition['value']['region'] == $region));

        return $condition['comparison'] == 'eq' ? $result : !$result;
    }

    /**
     * @param array $condition
     * @param array $data
     * @return bool
     */
    protected function cityNameCondition(array $condition, array $data)
    {
        $country = ifempty($data, 'address', 'country', '');
        $region = ifempty($data, 'address', 'region', '');
        $city = ifempty($data, 'address', 'city', '');

        $city = trim($city);
        $condition_city = trim((string)Hash::get($condition, 'value.city', ''));

        $result = (is_null($condition['value']['country']) || $condition['value']['country'] == $country) &&
            (is_null($condition['value']['region']) || $condition['value']['region'] == $region) &&
            mb_strtolower($condition_city) == mb_strtolower($city);

        return $condition['comparison'] == 'eq' ? $result : !$result;
    }

    /**
     * @param array $condition
     * @param array $data
     * @return bool
     */
    protected function orderWeightCondition(array $condition, array $data)
    {
        $weight = $this->totalItemsWeight((array)ifset($data, 'items', []));
        $value = $this->plugin->helper->toFloat($condition['value']);

        return $this->_comparisonProcessor($weight, $value, (string)Hash::get($condition, 'comparison', 'gte'));
    }

    /**
     * @param array $condition
     * @param array $data
     * @return bool
     */
    protected function orderPriceCondition(array $condition, array $data)
    {
        $price = (float)array_reduce((array)ifset($data, 'items', []), function ($total, $item) {
            $item_price = ifset($item, 'price', 0.0);
            $item_price = $item_price === null ? 0.0 : $this->plugin->helper->toFloat($item_price);
            $item_qty = (int)max(1, (int)ifempty($item, 'quantity', 1));
            return $total + round($item_qty * $item_price, 2);
        }, 0.0);
        $value = $this->plugin->helper->toFloat($condition['value']);

        return $price >= $value;
    }

    /**
     * @param array $items
     * @return float
     */
    protected function totalItemsWeight(array $items)
    {
        return (float)array_reduce($items, function ($total, $item) {
            $item_weight = ifset($item, 'weight', 0.0);
            $item_weight = $item_weight === null ? 0.0 : $this->plugin->helper->toFloat($item_weight);
            $item_qty = (int)max(1, (int)ifempty($item, 'quantity', 1));
            return $total + $item_qty * $item_weight;
        }, 0.0);
    }

    /**
     * @param array $items
     * @return float
     */
    protected function totalItemsPrice(array $items)
    {
        return (float)array_reduce($items, function ($total, $item) {
            $item_price = ifset($item, 'price', 0.0);
            $item_price = $item_price === null ? 0.0 : $this->plugin->helper->toFloat($item_price);
            $item_qty = (int)max(1, (int)ifempty($item, 'quantity', 1));
            return $total + $item_qty * $item_price;
        }, 0.0);
    }

    /**
     * @param $unit
     * @param array $grid
     * @param array $items
     * @return mixed|null
     */
    protected function calcPriceByGrid($unit, array $grid, array $items)
    {
        $grid_price = null;
        if ($unit === 'weight') {
            $precision = 3;
            $value = $this->totalItemsWeight($items);
        } elseif ($unit === 'price') {
            $precision = 2;
            $value = $this->totalItemsPrice($items);
        } else {
            return null;
        }

        array_walk($grid, function (&$gr, $key, $precision) {
            $gr['condition'] = round($this->plugin->helper->toFloat($gr['condition']), $precision);
            $gr['price'] = round($this->plugin->helper->toFloat($gr['price']), 2);
        }, $precision);

        usort($grid, function ($a, $b) {
            if ($a['condition'] == $b['condition']) {
                return 0;
            }
            return $a['condition'] > $b['condition'] ? 1 : -1;
        });

        foreach ($grid as $row) {
            if ($row['condition'] > $value) {
                break;
            }
            $grid_price = $row['price'];
        }

        return $grid_price;
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @param string $comparison
     * @return bool
     */
    protected function _comparisonProcessor($a, $b, $comparison)
    {
        switch ($comparison) {
            case 'eq' :
                return $a == $b;
                break;
            case 'neq':
                return $a != $b;
                break;
            case 'lt':
                return $a < $b;
                break;
            case 'lte':
                return $a <= $b;
                break;
            case 'gt':
                return $a > $b;
                break;
            case 'gte':
                return $a >= $b;
                break;
        }
        return false;
    }
}
