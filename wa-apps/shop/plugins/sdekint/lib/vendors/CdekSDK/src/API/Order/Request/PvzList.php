<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Request;

use SergeR\Webasyst\CdekSDK\API\Order\Contracts\OrderAPIRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\SerializableParams;
use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;

/**
 * Class PvzList
 * Запрос списка ПВЗ
 */
class PvzList implements OrderAPIRequest, SerializableParams
{
    use FillFromArray;

    /** @var string */
    protected $CityPostcode;

    /** @var int */
    protected $CityId;

    /** @var string */
    protected $Type;

    /** @var string */
    protected $CountryId;

    /** @var string */
    protected $RegionId;

    /** @var bool */
    protected $HaveCashless;

    /** @var bool */
    protected $AllowedCOD;

    /** @var bool */
    protected $IsDressingRoom;

    /** @var int */
    protected $WeightMax;

    /** @var string */
    protected $Lang;

    /** @var bool */
    protected $TakeOnly;

    /**
     * @param string $CityPostcode
     * @return PvzList
     */
    public function setCityPostcode($CityPostcode)
    {
        $this->CityPostcode = $CityPostcode;
        return $this;
    }

    /**
     * @param int $CityId
     * @return PvzList
     */
    public function setCityId($CityId)
    {
        $this->CityId = $CityId;
        return $this;
    }

    /**
     * @param string $Type
     * @return PvzList
     */
    public function setType($Type)
    {
        $this->Type = $Type;
        return $this;
    }

    /**
     * @param string $CountryId
     * @return PvzList
     */
    public function setCountryId($CountryId)
    {
        $this->CountryId = $CountryId;
        return $this;
    }

    /**
     * @param string $RegionId
     * @return PvzList
     */
    public function setRegionId($RegionId)
    {
        $this->RegionId = $RegionId;
        return $this;
    }

    /**
     * @param bool $HaveCashless
     * @return PvzList
     */
    public function setHaveCashless($HaveCashless)
    {
        $this->HaveCashless = $HaveCashless;
        return $this;
    }

    /**
     * @param bool $AllowedCOD
     * @return PvzList
     */
    public function setAllowedCOD($AllowedCOD)
    {
        $this->AllowedCOD = $AllowedCOD;
        return $this;
    }

    /**
     * @param bool $IsDressingRoom
     * @return PvzList
     */
    public function setIsDressingRoom($IsDressingRoom)
    {
        $this->IsDressingRoom = $IsDressingRoom;
        return $this;
    }

    /**
     * @param int $WeightMax
     * @return PvzList
     */
    public function setWeightMax($WeightMax)
    {
        $this->WeightMax = $WeightMax;
        return $this;
    }

    /**
     * @param string $Lang
     * @return PvzList
     */
    public function setLang($Lang)
    {
        $this->Lang = $Lang;
        return $this;
    }

    /**
     * @param bool $TakeOnly
     * @return PvzList
     */
    public function setTakeOnly($TakeOnly)
    {
        $this->TakeOnly = $TakeOnly;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return '/pvzlist/v1/xml';
    }

    /**
     * @return array
     */
    public function getFormParams()
    {
        $params = array();
        if ($this->CityId !== null) {
            $params['cityid'] = $this->CityId;
        } elseif ($this->CityPostcode !== null) {
            $params['citypostcode'] = $this->CityPostcode;
        }

        foreach (['Type', 'CountryId', 'RegionId', 'WeightMax', 'Lang'] as $item) {
            if ($this->$item !== null) {
                $params[strtolower($item)] = $this->$item;
            }
        }

        foreach (['HaveCashless', 'AllowedCOD', 'IsDressingRoom', 'TakeOnly'] as $item) {
            if ($this->$item !== null) {
                $params[strtolower($item)] = (int)$this->$item;
            }
        }

        return $params;
    }
}