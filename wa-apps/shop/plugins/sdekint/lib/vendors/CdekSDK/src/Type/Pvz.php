<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;

class Pvz
{
    use FillFromArray;

    /** @var string */
    protected $Code;

    /** @var string|null */
    protected $PostalCode;

    /** @var string */
    protected $Name;

    /** @var int */
    protected $CityCode;

    /** @var string */
    protected $City;

    /** @var string */
    protected $WorkTime;

    /** @var string */
    protected $Address;

    /** @var string */
    protected $Phone;

    /** @var string */
    protected $Note;

    /** @var float */
    protected $coordX;

    /** @var float */
    protected $coordY;

    /** @var WeightLimit|null */
    protected $WeightLimit;

    /** @var int */
    protected $CountryCode;

    /** @var string */
    protected $CountryName;

    /** @var int */
    protected $RegionCode;

    /** @var string */
    protected $RegionName;

    /** @var string */
    protected $FullAddress;

    /** @var bool */
    protected $IsDressingRoom;

    /** @var bool */
    protected $HaveCashless;

    /** @var bool */
    protected $AllowedCod;

    /** @var string */
    protected $NearestStation;

    /** @var string */
    protected $Site;

    /** @var string */
    protected $MetroStation;

    /** @var WorkTimeYCollection */
    protected $WorkTimeY;

    /** @var array */
    protected $OfficeImage = [];

    /** @var array */
    protected $OfficeHowGo = [];

    /** @var string */
    protected $Type='';

    /** @var string */
    protected $ownerCode='';

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param string $Code
     * @return Pvz
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->PostalCode;
    }

    /**
     * @param string|null $PostalCode
     * @return Pvz
     */
    public function setPostalCode($PostalCode)
    {
        $this->PostalCode = $PostalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     * @return Pvz
     */
    public function setName($Name)
    {
        $this->Name = $Name;
        return $this;
    }

    /**
     * @return int
     */
    public function getCityCode()
    {
        return $this->CityCode;
    }

    /**
     * @param int $CityCode
     * @return Pvz
     */
    public function setCityCode($CityCode)
    {
        $this->CityCode = (int)$CityCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->City;
    }

    /**
     * @param string $City
     * @return Pvz
     */
    public function setCity($City)
    {
        $this->City = $City;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkTime()
    {
        return $this->WorkTime;
    }

    /**
     * @param string $WorkTime
     * @return Pvz
     */
    public function setWorkTime($WorkTime)
    {
        $this->WorkTime = $WorkTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->Address;
    }

    /**
     * @param string $Address
     * @return Pvz
     */
    public function setAddress($Address)
    {
        $this->Address = $Address;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->Phone;
    }

    /**
     * @param string $Phone
     * @return Pvz
     */
    public function setPhone($Phone)
    {
        $this->Phone = $Phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->Note;
    }

    /**
     * @param string $Note
     * @return Pvz
     */
    public function setNote($Note)
    {
        $this->Note = $Note;
        return $this;
    }

    /**
     * @return float
     */
    public function getCoordX()
    {
        return $this->coordX;
    }

    /**
     * @param float $coordX
     * @return Pvz
     */
    public function setCoordX($coordX)
    {
        $this->coordX = (float)$coordX;
        return $this;
    }

    /**
     * @return float
     */
    public function getCoordY()
    {
        return $this->coordY;
    }

    /**
     * @param float $coordY
     * @return Pvz
     */
    public function setCoordY($coordY)
    {
        $this->coordY = (float)$coordY;
        return $this;
    }

    /**
     * @return WeightLimit|null
     */
    public function getWeightLimit()
    {
        return $this->WeightLimit;
    }

    /**
     * @param WeightLimit|array|null $WeightLimit
     * @return Pvz
     */
    public function setWeightLimit($WeightLimit)
    {
        if ($WeightLimit instanceof WeightLimit) {
            $this->WeightLimit = clone $WeightLimit;
        } elseif ($WeightLimit === null) {
            $this->WeightLimit = null;
        } elseif (is_array($WeightLimit)) {
            $this->WeightLimit = WeightLimit::fromArray($WeightLimit);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getCountryCode()
    {
        return $this->CountryCode;
    }

    /**
     * @param int $CountryCode
     * @return Pvz
     */
    public function setCountryCode($CountryCode)
    {
        $this->CountryCode = (int)$CountryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->CountryName;
    }

    /**
     * @param string $CountryName
     * @return Pvz
     */
    public function setCountryName($CountryName)
    {
        $this->CountryName = $CountryName;
        return $this;
    }

    /**
     * @return int
     */
    public function getRegionCode()
    {
        return $this->RegionCode;
    }

    /**
     * @param int $RegionCode
     * @return Pvz
     */
    public function setRegionCode($RegionCode)
    {
        $this->RegionCode = (int)$RegionCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegionName()
    {
        return $this->RegionName;
    }

    /**
     * @param string $RegionName
     * @return Pvz
     */
    public function setRegionName($RegionName)
    {
        $this->RegionName = $RegionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        return $this->FullAddress;
    }

    /**
     * @param string $FullAddress
     * @return Pvz
     */
    public function setFullAddress($FullAddress)
    {
        $this->FullAddress = $FullAddress;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDressingRoom()
    {
        return $this->IsDressingRoom;
    }

    /**
     * @param bool $IsDressingRoom
     * @return Pvz
     */
    public function setIsDressingRoom($IsDressingRoom)
    {
        $this->IsDressingRoom = $this->_string2bool($IsDressingRoom);
        return $this;
    }

    /**
     * @return bool
     */
    public function isHaveCashless()
    {
        return $this->HaveCashless;
    }

    /**
     * @param bool $HaveCashless
     * @return Pvz
     */
    public function setHaveCashless($HaveCashless)
    {
        $this->HaveCashless = $this->_string2bool($HaveCashless);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowedCod()
    {
        return $this->AllowedCod;
    }

    /**
     * @param bool $AllowedCod
     * @return Pvz
     */
    public function setAllowedCod($AllowedCod)
    {
        $this->AllowedCod = $this->_string2bool($AllowedCod);
        return $this;
    }

    /**
     * @return string
     */
    public function getNearestStation()
    {
        return $this->NearestStation;
    }

    /**
     * @param string $NearestStation
     * @return Pvz
     */
    public function setNearestStation($NearestStation)
    {
        $this->NearestStation = $NearestStation;
        return $this;
    }

    /**
     * @return string
     */
    public function getSite()
    {
        return $this->Site;
    }

    /**
     * @param string $Site
     * @return Pvz
     */
    public function setSite($Site)
    {
        $this->Site = $Site;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetroStation()
    {
        return $this->MetroStation;
    }

    /**
     * @param string $MetroStation
     * @return Pvz
     */
    public function setMetroStation($MetroStation)
    {
        $this->MetroStation = $MetroStation;
        return $this;
    }

    /**
     * @return WorkTimeYCollection
     */
    public function getWorkTimeY()
    {
        return $this->WorkTimeY;
    }

    /**
     * @param WorkTimeYCollection|array $WorkTimeY
     * @return Pvz
     */
    public function setWorkTimeY($WorkTimeY)
    {
        if ($WorkTimeY instanceof WorkTimeYCollection) {
            $this->WorkTimeY = clone $WorkTimeY;
        } elseif (is_array($WorkTimeY)) {
            $this->WorkTimeY = WorkTimeYCollection::fromXmlArray($WorkTimeY);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getOfficeImage()
    {
        return $this->OfficeImage;
    }

    /**
     * @param array $OfficeImage
     * @return Pvz
     */
    public function setOfficeImage(array $OfficeImage)
    {
        $this->OfficeImage = $OfficeImage;
        return $this;
    }

    /**
     * @return array
     */
    public function getOfficeHowGo()
    {
        return $this->OfficeHowGo;
    }

    /**
     * @param array $OfficeHowGo
     * @return Pvz
     */
    public function setOfficeHowGo(array $OfficeHowGo)
    {
        $this->OfficeHowGo = $OfficeHowGo;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param string $Type
     * @return Pvz
     */
    public function setType($Type)
    {
        $this->Type = $Type;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerCode()
    {
        return (string)$this->ownerCode;
    }

    /**
     * @param string $ownerCode
     * @return Pvz
     */
    public function setOwnerCode($ownerCode)
    {
        $this->ownerCode = $ownerCode;
        return $this;
    }

    protected function _string2bool($val)
    {
        if (in_array(mb_strtolower($val, 'UTF-8'), ['да', 'yes', 'есть', 'true', '1'])) {
            return true;
        }

        return false;
    }
}