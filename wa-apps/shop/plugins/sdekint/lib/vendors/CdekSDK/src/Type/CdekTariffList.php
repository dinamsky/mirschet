<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

class CdekTariffList
{
    protected $tariffs = array(
        array(
            'id'          => 1,
            'name'        => 'Экспресс лайт',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка по России документов и грузов до 30 кг.',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 3,
            'name'        => 'Супер-экспресс до 18',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Срочная доставка',
            'description' => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу',
            'min_weight'  => 0,
            'max_weight'  => null,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 5,
            'name'        => 'Экономичный экспресс',
            'from'        => 'stock',
            'to'          => 'stock',
            'group'       => 'Экономичная доставка',
            'description' => 'Недорогая доставка грузов по России ЖД и автотранспортом (доставка грузов с увеличением сроков)',
            'min_weight'  => 0,
            'max_weight'  => null,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 7,
            'name'        => 'Международный экспресс документы',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Международная доставка',
            'description' => 'Экспресс-доставка за/из-за границы документов и писем',
            'min_weight'  => 0,
            'max_weight'  => 1.5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 8,
            'name'        => 'Международный экспресс грузы',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Международная доставка',
            'description' => 'Экспресс-доставка за/из-за границы грузов и посылок от 0,5 кг до 30 кг',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 10,
            'name'        => 'Экспресс лайт',
            'from'        => 'stock',
            'to'          => 'stock',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка по России документов и грузов',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 11,
            'name'        => 'Экспресс лайт',
            'from'        => 'stock',
            'to'          => 'door',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка по России документов и грузов',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 12,
            'name'        => 'Экспресс лайт',
            'from'        => 'door',
            'to'          => 'stock',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка по России документов и грузов',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 15,
            'name'        => 'Экспресс тяжеловесы',
            'from'        => 'stock',
            'to'          => 'stock',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка грузов по России',
            'min_weight'  => 30,
            'max_weight'  => null,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 16,
            'name'        => 'Экспресс тяжеловесы',
            'from'        => 'stock',
            'to'          => 'door',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка грузов по России',
            'min_weight'  => 30,
            'max_weight'  => null,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 17,
            'name'        => 'Экспресс тяжеловесы',
            'from'        => 'door',
            'to'          => 'stock',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка грузов по России',
            'min_weight'  => 30,
            'max_weight'  => null,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 18,
            'name'        => 'Экспресс тяжеловесы',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Экспресс',
            'description' => 'Классическая экспресс-доставка грузов по России',
            'min_weight'  => 30,
            'max_weight'  => null,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 57,
            'name'        => 'Супер-экспресс до 9',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Срочная доставка',
            'description' => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток)',
            'min_weight'  => 0,
            'max_weight'  => 5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 58,
            'name'        => 'Супер-экспресс до 10',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Срочная доставка',
            'description' => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток)',
            'min_weight'  => 0,
            'max_weight'  => 5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 59,
            'name'        => 'Супер-экспресс до 12',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Срочная доставка',
            'description' => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток)',
            'min_weight'  => 0,
            'max_weight'  => 5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 60,
            'name'        => 'Супер-экспресс до 14',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Срочная доставка',
            'description' => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток)',
            'min_weight'  => 0,
            'max_weight'  => 5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 61,
            'name'        => 'Супер-экспресс до 16',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Срочная доставка',
            'description' => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток)',
            'min_weight'  => 0,
            'max_weight'  => 5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 62,
            'name'        => 'Магистральный экспресс',
            'from'        => 'stock',
            'to'          => 'stock',
            'group'       => 'Экономичная доставка',
            'description' => 'Быстрая экономичная доставка грузов по России',
            'min_weight'  => 0,
            'max_weight'  => 5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 63,
            'name'        => 'Магистральный супер-экспресс',
            'from'        => 'stock',
            'to'          => 'stock',
            'group'       => 'Экономичная доставка',
            'description' => 'Быстрая экономичная доставка грузов к определенному часу',
            'min_weight'  => 0,
            'max_weight'  => 5,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 136,
            'name'        => 'Посылка',
            'from'        => 'stock',
            'to'          => 'stock',
            'group'       => 'Посылка',
            'description' => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 137,
            'name'        => 'Посылка',
            'from'        => 'stock',
            'to'          => 'door',
            'group'       => 'Посылка',
            'description' => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 138,
            'name'        => 'Посылка',
            'from'        => 'door',
            'to'          => 'stock',
            'group'       => 'Посылка',
            'description' => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 139,
            'name'        => 'Посылка',
            'from'        => 'door',
            'to'          => 'door',
            'group'       => 'Посылка',
            'description' => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю',
            'min_weight'  => 0,
            'max_weight'  => 30,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 158,
            'name'        => 'Забор для ИМ',
            'from'        => 'door',
            'to'          => 'stock',
            'group'       => 'Забор товаров',
            'description' => 'Тариф для забора товаров от ИМ',
            'min_weight'  => 0,
            'max_weight'  => null,
            'hidden'      => true,
            'deprecated'  => false
        ),
        array(
            'id'          => 233,
            'name'        => 'Экономичная Посылка',
            'from'        => 'stock',
            'to'          => 'door',
            'group'       => 'Экономичная Посылка',
            'description' => 'Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю. Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым.',
            'min_weight'  => 0,
            'max_weight'  => 50,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 234,
            'name'        => 'Экономичная Посылка',
            'from'        => 'stock',
            'to'          => 'stock',
            'group'       => 'Экономичная Посылка',
            'description' => 'Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю. Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым.',
            'min_weight'  => 0,
            'max_weight'  => 50,
            'hidden'      => false,
            'deprecated'  => false
        ),
        array(
            'id'          => 301,
            'name'        => 'До постамата InPost',
            'from'        => 'door',
            'to'          => 'postomat',
            'group'       => 'InPost',
            'description' => 'Услуга доставки товаров по России с использованием постоматов. Для компаний, осуществляющих дистанционную торговлю',
            'min_weight'  => 0,
            'max_weight'  => 20,
            'hidden'      => true,
            'deprecated'  => true
        ),
        array(
            'id'          => 302,
            'name'        => 'До постамата InPost',
            'from'        => 'stock',
            'to'          => 'postomat',
            'group'       => 'InPost',
            'description' => 'Услуга доставки товаров по России с использованием постоматов. Для компаний, осуществляющих дистанционную торговлю',
            'min_weight'  => 0,
            'max_weight'  => 20,
            'hidden'      => true,
            'deprecated'  => true
        ),
    );
}