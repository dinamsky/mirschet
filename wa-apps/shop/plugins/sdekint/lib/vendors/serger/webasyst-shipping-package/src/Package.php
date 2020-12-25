<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\Shipping\Packages;

use PhpUnitsOfMeasure\Exception\NonNumericValue;
use PhpUnitsOfMeasure\Exception\NonStringUnitName;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class Package
{
    /** @var string */
    protected $weight_unit = 'g';

    /** @var string */
    protected $length_unit = 'm';

    /** @var int */
    protected $weight_decimals = 0;

    /** @var int */
    protected $length_decimals = 0;

    /** @var string */
    protected $unit_divider = 'x';

    /** @var string */
    protected $decimal_divider = '.';

    /** @var bool */
    protected $trim_decimal_zeroes = true;

    /** @var Mass */
    protected $min_weight = 0;

    /** @var Length */
    protected $length = 0;

    /** @var Length */
    protected $width = 0;

    /** @var Length */
    protected $height = 0;

    /**
     * @return string
     */
    public function getWeightUnit()
    {
        return $this->weight_unit;
    }

    /**
     * @param string $weight_unit
     * @return Package
     */
    public function setWeightUnit($weight_unit)
    {

        $this->weight_unit = $weight_unit;
        return $this;
    }

    /**
     * @return string
     */
    public function getLengthUnit()
    {
        return $this->length_unit;
    }

    /**
     * @param string $length_unit
     * @return Package
     */
    public function setLengthUnit($length_unit)
    {
        $this->length_unit = $length_unit;
        return $this;
    }

    /**
     * @return Mass
     */
    public function getMinWeight()
    {
        return $this->min_weight;
    }

    /**
     * @return float
     */
    public function getNativeMinWeight()
    {
        return $this->min_weight->toNativeUnit();
    }

    /**
     * @param Mass $min_weight
     * @return Package
     */
    public function setMinWeight($min_weight)
    {
        $this->min_weight = $min_weight;
        return $this;
    }

    /**
     * @return Length
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param Length $length
     * @return Package
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return Length
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param Length $width
     * @return Package
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return Length
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param Length $height
     * @return Package
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeightDecimals()
    {
        return $this->weight_decimals;
    }

    /**
     * @param int $weight_decimals
     * @return Package
     */
    public function setWeightDecimals($weight_decimals)
    {
        $this->weight_decimals = $weight_decimals;
        return $this;
    }

    /**
     * @return int
     */
    public function getLengthDecimals()
    {
        return $this->length_decimals;
    }

    /**
     * @param int $length_decimals
     * @return Package
     */
    public function setLengthDecimals($length_decimals)
    {
        $this->length_decimals = $length_decimals;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTrimDecimalZeroes()
    {
        return $this->trim_decimal_zeroes;
    }

    /**
     * @param bool $trim_decimal_zeroes
     */
    public function setTrimDecimalZeroes($trim_decimal_zeroes)
    {
        $this->trim_decimal_zeroes = $trim_decimal_zeroes;
    }

    /**
     * @return string
     */
    public function getUnitDivider()
    {
        return $this->unit_divider;
    }

    /**
     * @param string $unit_divider
     * @return Package
     */
    public function setUnitDivider($unit_divider)
    {
        $this->unit_divider = $unit_divider;
        return $this;
    }

    /**
     * @return string
     */
    public function getDecimalDivider()
    {
        return $this->decimal_divider;
    }

    /**
     * @param string $decimal_divider
     * @return Package
     */
    public function setDecimalDivider($decimal_divider)
    {
        $this->decimal_divider = $decimal_divider;
        return $this;
    }

    /**
     * @return Volume
     * @throws NonNumericValue
     * @throws NonStringUnitName
     */
    public function getVolume()
    {
        return new Volume($this->length->toUnit($this->length_unit) * $this->width->toUnit($this->length_unit) * $this->height->toUnit($this->length_unit), $this->length_unit . '^3');
    }

    public function stringify($unit = null, $len_decs = null, $unit_divider=null, $dec_divider = null, $trim_zeroes = null)
    {
        $unit = $unit === null ? $this->length_unit : $unit;
        $len_decs = $len_decs === null ? $this->length_decimals : $len_decs;
        $dec_divider = $dec_divider === null ? $this->decimal_divider : $dec_divider;
        $trim_zeroes = $trim_zeroes === null ? $this->trim_decimal_zeroes : $trim_zeroes;
        $unit_divider = $unit_divider === null ? $this->unit_divider : $unit_divider;

        $length = $this->length->toUnit($unit);
        $width = $this->width->toUnit($unit);
        $height = $this->height->toUnit($unit);

        foreach ([$length, $width, $height] as &$_unit) {
            $_unit = number_format($_unit, $len_decs, $dec_divider, '');
            if ($trim_zeroes && (strpos($_unit, $dec_divider) !== false)) {
                $_unit = rtrim(rtrim($_unit, '0'), $dec_divider);
            }
        }
        unset($_unit);
        return $length . $unit_divider . $width . $unit_divider . $height;
    }

    public function __toString()
    {
        return $this->stringify();
    }
}