<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use InvalidArgumentException;
use PhpUnitsOfMeasure\Exception\NonNumericValue;
use PhpUnitsOfMeasure\Exception\NonStringUnitName;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\Webasyst\Shipping\Packages\Package;
use SergeR\CakeUtility\Hash;

class OrderPackage extends Package implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var string */
    protected $Number;

    /** @var string */
    protected $BarCode;

    /** @var Mass */
    protected $Weight;

    /** @var OrderItemCollection */
    protected $Items;

    /** @var Length */
    protected $SizeA;

    /** @var Length */
    protected $SizeB;

    /** @var Length */
    protected $SizeC;

    public function __construct()
    {
        $this->Items = new OrderItemCollection();
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * @param string $Number
     * @return OrderPackage
     */
    public function setNumber($Number)
    {
        $this->Number = $Number;
        return $this;
    }

    /**
     * @return string
     */
    public function getBarCode()
    {
        return $this->BarCode;
    }

    /**
     * @param string $BarCode
     * @return OrderPackage
     */
    public function setBarCode($BarCode)
    {
        $this->BarCode = $BarCode;
        return $this;
    }

    /**
     * @param $weight
     * @return $this
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws InvalidArgumentException
     */
    public function setWeight($weight)
    {
        if(is_scalar($weight)) {
            $weight = (int)$weight;
            $this->Weight = new Mass($weight, 'g');
        } elseif ($weight instanceof Mass) {
            $this->Weight = clone $weight;
        } else {
            throw new InvalidArgumentException('Значение веса упаковки должно быль либо числом в граммах, либо объектом Mass');
        }
        return $this;
    }

    /**
     * @return Mass
     * @throws NonNumericValue
     * @throws NonStringUnitName
     */
    public function getWeight()
    {
        if($this->Weight === null) {
            $weight = 0;
            /** @var OrderItem $item */
            foreach ($this->Items as $item) {
                $weight += $item->getWeight() * $item->getAmount();
            }

            return new Mass($weight, 'g');
        }

        return $this->Weight;
    }

    /**
     * @param OrderItemCollection $items
     * @return $this
     */
    public function setItems(OrderItemCollection $items)
    {
        $this->Items = clone $items;
        return $this;
    }

    /**
     * @return OrderItemCollection
     */
    public function getItems()
    {
        return $this->Items;
    }

    public function addItem(OrderItem $item)
    {
        $this->Items->add(clone $item);
        return $this;
    }

    /**
     * @param $value
     * @return OrderPackage
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws InvalidArgumentException
     */
    public function setSizeB($value)
    {
        return $this->_setDimension('SizeB', $value);
    }

    /**
     * @param $value
     * @return OrderPackage
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws InvalidArgumentException
     */
    public function setSizeC($value)
    {
        return $this->_setDimension('SizeC', $value);
    }

    /**
     * @return Length|null
     */
    public function getSizeA()
    {
        return $this->_getDimension('SizeA');
    }

    /**
     * @return Length|null
     */
    public function getSizeB()
    {
        return $this->_getDimension('SizeB');
    }

    /**
     * @return Length|null
     */
    public function getSizeC()
    {
        return $this->_getDimension('SizeC');
    }

    /**
     * @param $value
     * @return OrderPackage
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws InvalidArgumentException
     */
    public function setSizeA($value)
    {
        return $this->_setDimension('SizeA', $value);
    }

    /**
     * @param array $options
     * @return array
     * @throws NonNumericValue
     * @throws NonStringUnitName
     */
    public function toXmlArray(array $options=[])
    {
        $arr = array(
            '@Number' => $this->getNumber(),
            '@BarCode' => $this->getBarCode(),
            '@Weight' => $this->getWeight()->toUnit('g'),
            'Item' => $this->getItems()->toXmlArray(['tag'=>null])
        );

        if($this->validateDimensions() === true) {
            $arr += array(
                '@SizeA'=>(int)$this->SizeA->toUnit('cm'),
                '@SizeB'=>(int)$this->SizeB->toUnit('cm'),
                '@SizeC'=>(int)$this->SizeC->toUnit('cm')
            );
        }

        $tag = Hash::get($options, 'tag');
        if($tag !== null) {
            $arr = array($tag => $arr);
        }

        return $arr;
    }

    /**
     * @param string $var
     * @param int|string|Length $value
     * @return $this
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws InvalidArgumentException
     */
    protected function _setDimension($var, $value)
    {
        if(is_scalar($value)) {
            $value = new Length((int)$value, 'cm');
        }elseif (!($value instanceof Length)) {
            throw new InvalidArgumentException('Размер должен быть числом сантиметров или объектом типа Length');
        }

        $this->$var = clone $value;

        return $this;
    }

    /**
     * @param $var
     * @return Length|null
     */
    protected function _getDimension($var)
    {
        return $this->$var;
    }

    /**
     * @return array|bool
     */
    public function validateDimensions() {
        $errors = array();
        foreach (['SizeA', 'SizeB', 'SizeC'] as $item) {
            $value =  $this->_getDimension($item);
            if($value === null) {
                $errors[] = "Размер $item должен быть указан";
            }
            if(!($value instanceof Length)) {
                $errors[] = "Размер $item должен быть объектом типа Length";
            } else {
                $size = $value->toUnit('cm');

                if (($size < 1) || ($size > 1500)) {
                    $errors[] = "Размер $item должен быть в пределах от 1 до 1500 см.";
                }
            }
        }

        return empty($errors) ? true : $errors;
    }
}