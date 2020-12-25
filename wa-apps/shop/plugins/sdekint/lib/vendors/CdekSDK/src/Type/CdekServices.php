<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\CakeUtility\Hash;

/**
 * Class CdekServices
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class CdekServices
{
    /**
     * @var array
     */
    protected $_services_arr = array(
        ['Code' => 2, 'Name' => 'Страхование', 'Description' => 'Обеспечение страховой защиты посылки. Размер дополнительного сбора страхования вычисляется от размера объявленной стоимости отправления', 'CalcRequiresParam' => true],
        ['Code' => 7, 'Name' => 'Опасный груз', 'Description' => 'Кроме обычных документов и грузов, компания СДЭК готова доставить отправления, содержащие опасные грузы (кроме запрещенных к перевозке). В связи с определенным риском стоимость доставки грузов, относящихся к категории опасных, увеличивается в 1,5 раза'],
        ['Code' => 16, 'Name' => 'Забор в городе отправителе', 'Description' => 'Дополнительная услуга забора груза в городе отправителя, при условии, что тариф доставки с режимом «от склада» (не доступна для тарифов Посылка)'],
        ['Code' => 17, 'Name' => 'Доставка в городе получателе', 'Description' => 'Дополнительная услуга доставки груза в городе получателя, при условии, что тариф доставки с режимом «до склада» (только для тарифов «Магистральный», «Магистральный супер-экспресс»)'],
        ['Code' => 24, 'Name' => 'Упаковка 1', 'Description' => 'Стоимость коробки размером 310*215*280мм (для грузов до 10 кг)', 'CalcRequiresParam' => true],
        ['Code' => 25, 'Name' => 'Упаковка 2', 'Description' => 'Стоимость коробки размером 430*310*280мм (для грузов до 15 кг)', 'CalcRequiresParam' => true],
        ['Code' => 30, 'Name' => 'Примерка на дому', 'Description' => 'Курьер доставляет покупателю несколько единиц товара (одежда, обувь и пр.) для примерки. Время ожидания курьера в этом случае составляет 30 минут'],
        ['Code' => 31, 'Name' => 'Доставка лично в руки', 'Description' => 'Компания СДЭК предлагает воспользоваться услугой "Доставка лично в руки" всем клиентам, которым важно, чтобы отправление было передано получателю только на основании документа, удостоверяющего личность. Услуга не совместима с тарифами "Супер-экспресс" и "Блиц-экспресс"'],
        ['Code' => 32, 'Name' => 'Скан документов', 'Description' => 'Для подтверждения факта доставки мы можем предоставить Вам скан документов с подписью получателя'],
        ['Code' => 36, 'Name' => 'Частичная доставка', 'Description' => 'Во время доставки товара покупатель может отказаться от одной или нескольких позиций, и выкупить только часть заказа'],
        ['Code' => 37, 'Name' => 'Осмотр вложения', 'Description' => 'Проверка покупателем содержимого заказа до его оплаты (вскрытие посылки)'],
    );

    /**
     * @param $id
     * @return CdekService|null
     */
    public static function getById($id)
    {
        $id = (int)$id;
        if (!$id) {
            return null;
        }

        $obj = new self();
        $s = Hash::extract($obj->_services_arr, "{n}[Code=$id]");
        $s = array_shift($s);
        if (!$s) {
            return null;
        }

        return CdekService::fromArray($s);
    }
}