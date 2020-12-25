<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.2.0
 * @copyright Serge Rodovnichenko, 2015-2019
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint.controller
 */

use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\API\Calculator\Request\CalculationTariffListRequest;

/**
 * Class shopSdekintPluginTariffsGetController
 */
class shopSdekintPluginTariffsGetController extends waJsonController
{
    /** @var null|shopSdekintPlugin */
    protected $plugin = null;

    /**
     * @throws waException
     */
    protected function preExecute()
    {
        $this->plugin = wa('shop')->getPlugin('sdekint');
    }

    /**
     * main method
     */
    public function execute()
    {
        $SdekCalcApi = $this->plugin->getCalculatorApiClient();

        $conditions = $this->getRequest()->get('params', array(), waRequest::TYPE_ARRAY);
        $tariffs = $this->plugin->tariffs;
        $tariffs = Hash::extract($tariffs, '{n}[enabled=true].id');
        $conditions['id'] = $tariffs;
        $tariffs = (new shopSdekintPluginTariffModel)->find($conditions);
        $tariffs = Hash::combine(array_values($tariffs), '{n}.id', '{n}');

        $goods = array();
        foreach ((array)$conditions['packages'] as $package) {
            $goods[] = $package;
        }

        $request = (new CalculationTariffListRequest)
            ->setDateExecute(new DateTimeImmutable)
            ->setSenderCityId($conditions['from_city'])
            ->setReceiverCityId($conditions['to_city'])
            ->setTariff(array_column($tariffs, 'id'))
            ->setGoods($goods);

        try {
            $result = $SdekCalcApi->calc_tarifflist($request);
            if ($result->isError()) {
                $errors = $result->getErrors();
                $first_error = (array)reset($errors);
                throw new waException(
                    (string)Hash::get($first_error, 'message', 'Неизвестная ошибка'),
                    (int)Hash::get($first_error, 'code')
                );
            }
        } catch (Exception $e) {
            $this->setError('Ошибка при обращении к серверу СДЭК для расчета доставки.');
            $this->setError($e->getMessage(), $e->getCode());
            return;
        }

        $calculated = [];

        foreach (($prices = $result->getResults()) as $price) {
            if (!$price->isError() && isset($tariffs[$price->getTariffId()])) {
                $calculated[] = $tariffs[$price->getTariffId()] + array_filter($price->toArray(), function ($v) {
                        return $v !== null;
                    });
            }
        }

        $sort_order = array_flip(array_keys($tariffs));
        usort($calculated, function ($a, $b) use ($sort_order) {
            $av = Hash::get($sort_order, $a['id'], 999999);
            $bv = Hash::get($sort_order, $b['id'], 999999);
            if ($av == $bv) {
                return 0;
            }
            return $av > $bv ? 1 : -1;
        });

        $this->response['tariffs'] = $calculated;
    }
}
