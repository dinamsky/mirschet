<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;

class WeightLimit
{
    use FillFromArray;

    /** @var Mass|null */
    protected $WeightMin;

    /** @var Mass|null */
    protected $WeightMax;

    /**
     * @param string $unit
     * @return float
     */
    public function getWeightMinValue($unit = 'kg')
    {
        if (!($this->WeightMin instanceof Mass)) {
            return null;
        }
        return $this->WeightMin->toUnit($unit);
    }

    /**
     * @return Mass
     */
    public function getWeightMin()
    {
        if (!($this->WeightMin instanceof Mass)) {
            return null;
        }
        return $this->WeightMin;
    }

    /**
     * @param Mass|float|null $WeightMin
     * @return $this
     * @throws \PhpUnitsOfMeasure\Exception\NonNumericValue
     * @throws \PhpUnitsOfMeasure\Exception\NonStringUnitName
     * @throws \InvalidArgumentException
     */
    public function setWeightMin($WeightMin = null)
    {
        if ($WeightMin === null) {
            $this->WeightMin = null;
        } elseif ($WeightMin instanceof Mass) {
            $this->WeightMin = clone $WeightMin;
        } else {
            $this->WeightMin = new Mass($WeightMin, 'kg');
        }

        return $this;
    }

    /**
     * @return Mass|null
     */
    public function getWeightMax()
    {
        if (!($this->WeightMax instanceof Mass)) {
            return null;
        }
        return $this->WeightMax;
    }

    /**
     * @param string $unit
     * @return float|null
     */
    public function getWeightMaxValue($unit = 'kg')
    {
        return $this->WeightMax->toUnit($unit);
    }

    /**
     * @param Mass|float|null $WeightMax
     * @return WeightLimit
     * @throws \PhpUnitsOfMeasure\Exception\NonNumericValue
     * @throws \PhpUnitsOfMeasure\Exception\NonStringUnitName
     */
    public function setWeightMax($WeightMax = null)
    {
        if ($WeightMax === null) {
            $this->WeightMax = null;
        } elseif ($WeightMax instanceof Mass) {
            $this->WeightMax = clone $WeightMax;
        } else {
            $this->WeightMax = new Mass($WeightMax, 'kg');
        }

        return $this;
    }

    /**
     * @return float|null
     */
    public function min()
    {
        return $this->getWeightMinValue();
    }

    /**
     * @return float|null
     */
    public function max()
    {
        return $this->getWeightMaxValue();
    }
}