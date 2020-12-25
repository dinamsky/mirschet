<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;

/**
 * Используется в API калькулятора (подсказки города). Элемент коллекции результата поиска города по строке
 *
 * Class Geoname
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class Geoname
{
    use FillFromArray;

    /** @var int */
    protected $id = 0;

    /** @var string[] массив почтовых индексов города */
    protected $postCodeArray=[];

    /** @var string название города в базе СДЭК */
    protected $cityName='';

    /** @var string ID региона по базе СДЭК */
    protected $regionId='';

    /** @var string Название региона по базе СДЭК */
    protected $regionName='';

    /** @var string ID страны по базе СДЭК */
    protected $countryId='';

    /** @var string Название страны по базе СДЭК */
    protected $countryName='';

    /** @var string Двухсимвольный код страны ISO3166-2 */
    protected $countryIso='';

    /** @var string полное название с регионом и сраной */
    protected $name='';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getPostCodeArray()
    {
        return $this->postCodeArray;
    }

    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * @return string
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * @return string
     */
    public function getRegionName()
    {
        return $this->regionName;
    }

    /**
     * @return string
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * @return string
     */
    public function getCountryIso()
    {
        return $this->countryIso;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}