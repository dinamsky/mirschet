<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Hash;

class CourierCall implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var \DateTimeImmutable */
    protected $Date;

    /** @var string */
    protected $TimeBeg;

    /** @var string */
    protected $TimeEnd;

    /** @var string */
    protected $LunchBeg;

    /** @var string */
    protected $LunchEnd;

    /** @var int */
    protected $SendCityCode;

    /** @var string */
    protected $SendCityPostCode;

    /** @var string */
    protected $SendCountryCode;

    /** @var string */
    protected $SendCityName;

    /** @var string */
    protected $SendPhone;

    /** @var string */
    protected $SenderName;

    /** @var string */
    protected $Comment;

    /** @var StreetAddress */
    protected $SendAddress;

    /**
     * @return \DateTimeImmutable
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * @param \DateTimeImmutable $Date
     * @return CourierCall
     */
    public function setDate($Date)
    {
        $this->Date = $Date;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeBeg()
    {
        return $this->TimeBeg;
    }

    /**
     * @param string $TimeBeg
     * @return CourierCall
     */
    public function setTimeBeg($TimeBeg)
    {
        $this->TimeBeg = $TimeBeg;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeEnd()
    {
        return $this->TimeEnd;
    }

    /**
     * @param string $TimeEnd
     * @return CourierCall
     */
    public function setTimeEnd($TimeEnd)
    {
        $this->TimeEnd = $TimeEnd;
        return $this;
    }

    /**
     * @return string
     */
    public function getLunchBeg()
    {
        return $this->LunchBeg;
    }

    /**
     * @param string $LunchBeg
     * @return CourierCall
     */
    public function setLunchBeg($LunchBeg)
    {
        $this->LunchBeg = $LunchBeg;
        return $this;
    }

    /**
     * @return string
     */
    public function getLunchEnd()
    {
        return $this->LunchEnd;
    }

    /**
     * @param string $LunchEnd
     * @return CourierCall
     */
    public function setLunchEnd($LunchEnd)
    {
        $this->LunchEnd = $LunchEnd;
        return $this;
    }

    /**
     * @return int
     */
    public function getSendCityCode()
    {
        return $this->SendCityCode;
    }

    /**
     * @param int $SendCityCode
     * @return CourierCall
     */
    public function setSendCityCode($SendCityCode)
    {
        $this->SendCityCode = $SendCityCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSendCityPostCode()
    {
        return $this->SendCityPostCode;
    }

    /**
     * @param string $SendCityPostCode
     * @return CourierCall
     */
    public function setSendCityPostCode($SendCityPostCode)
    {
        $this->SendCityPostCode = $SendCityPostCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSendCountryCode()
    {
        return $this->SendCountryCode;
    }

    /**
     * @param string $SendCountryCode
     * @return CourierCall
     */
    public function setSendCountryCode($SendCountryCode)
    {
        $this->SendCountryCode = $SendCountryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSendCityName()
    {
        return $this->SendCityName;
    }

    /**
     * @param string $SendCityName
     * @return CourierCall
     */
    public function setSendCityName($SendCityName)
    {
        $this->SendCityName = $SendCityName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSendPhone()
    {
        return $this->SendPhone;
    }

    /**
     * @param string $SendPhone
     * @return CourierCall
     */
    public function setSendPhone($SendPhone)
    {
        $this->SendPhone = $SendPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderName()
    {
        return $this->SenderName;
    }

    /**
     * @param string $SenderName
     * @return CourierCall
     */
    public function setSenderName($SenderName)
    {
        $this->SenderName = $SenderName;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->Comment;
    }

    /**
     * @param string $Comment
     * @return CourierCall
     */
    public function setComment($Comment)
    {
        $this->Comment = $Comment;
        return $this;
    }

    /**
     * @return StreetAddress
     */
    public function getSendAddress()
    {
        return $this->SendAddress;
    }

    /**
     * @param StreetAddress $SendAddress
     * @return CourierCall
     */
    public function setSendAddress($SendAddress)
    {
        $this->SendAddress = clone $SendAddress;
        return $this;
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $options = array_merge(['tag' => 'Call'], $options);
        $tag = Hash::get($options, 'tag');

        $arr = array(
            '@Date'       => $this->getDate()->format('Y-m-d'),
            '@TimeBeg'    => $this->getTimeBeg(),
            '@TimeEnd'    => $this->getTimeEnd(),
            '@SendPhone'  => $this->getSendPhone(),
            '@SenderName' => $this->getSenderName(),
            'SendAddress' => $this->getSendAddress()->toXmlArray(['tag' => null])
        );

        if (($city_code = $this->getSendCityCode())) {
            $arr['@SendCityCode'] = $city_code;
        } else {
            $arr['@SendCityPostCode'] = $this->getSendCityPostCode();
            if (($country_code = $this->getSendCountryCode())) {
                $arr['@SendCountryCode'] = $country_code;
            }
            if (($city_name = $this->getSendCityName())) {
                $arr['@SendCityName'] = $city_name;
            }
        }

        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}