<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Hash;

class ShopOrder implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var string */
    protected $Number;

    /** @var int */
    protected $SendCityCode;

    /** @var int */
    protected $RecCityCode;

    /** @var string */
    protected $SendCityPostCode;

    /** @var string */
    protected $RecCityPostCode;

    /** @var string */
    protected $SendCountryCode;

    /** @var string */
    protected $RecCountryCode;

    /** @var string */
    protected $SendCityName;

    /** @var string */
    protected $RecCityName;

    /** @var OrderSender */
    protected $Sender;

    /** @var string */
    protected $RecipientName;

    /** @var string */
    protected $RecipientEmail;

    /** @var string */
    protected $Phone;

    /** @var int */
    protected $TariffTypeCode;

    /** @var float */
    protected $DeliveryRecipientCost;

    /** @var string */
    protected $DeliveryRecipientVATRate;

    /** @var string */
    protected $DeliveryRecipientVATSum;

    /** @var string */
    protected $RecipientCurrency;

    /** @var string */
    protected $ItemsCurrency;

    /** @var string */
    protected $SellerName;

    protected $Seller = ['name' => '', 'address' => '', 'phone' => '', 'ownership_form' => null];

    /** @var string */
    protected $Comment;

    /** @var OrderAddress */
    protected $Address;

    /** @var OrderPackageCollection */
    protected $Package;

    /** @var array */
    protected $AddService = [];

    /** @var ScheduleAttemptCollection */
    protected $Schedule;

    public function __construct()
    {
        $this->Address = new OrderAddress();
        $this->Package = new OrderPackageCollection();
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
     * @return $this
     */
    public function setNumber($Number)
    {
        $this->Number = $Number;
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
     * @return $this
     */
    public function setSendCityCode($SendCityCode)
    {
        $this->SendCityCode = $SendCityCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecCityCode()
    {
        return $this->RecCityCode;
    }

    /**
     * @param int $RecCityCode
     * @return $this
     */
    public function setRecCityCode($RecCityCode)
    {
        $this->RecCityCode = $RecCityCode;
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
     * @return $this
     */
    public function setSendCityPostCode($SendCityPostCode)
    {
        $this->SendCityPostCode = $SendCityPostCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecCityPostCode()
    {
        return $this->RecCityPostCode;
    }

    /**
     * @param string $RecCityPostCode
     * @return $this
     */
    public function setRecCityPostCode($RecCityPostCode)
    {
        $this->RecCityPostCode = $RecCityPostCode;
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
     * @return $this
     */
    public function setSendCountryCode($SendCountryCode)
    {
        $this->SendCountryCode = $SendCountryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecCountryCode()
    {
        return $this->RecCountryCode;
    }

    /**
     * @param string $RecCountryCode
     * @return $this
     */
    public function setRecCountryCode($RecCountryCode)
    {
        $this->RecCountryCode = $RecCountryCode;
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
     * @return $this
     */
    public function setSendCityName($SendCityName)
    {
        $this->SendCityName = $SendCityName;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecCityName()
    {
        return $this->RecCityName;
    }

    /**
     * @param string $RecCityName
     * @return $this
     */
    public function setRecCityName($RecCityName)
    {
        $this->RecCityName = $RecCityName;
        return $this;
    }

    /**
     * @return OrderSender
     */
    public function getSender()
    {
        return $this->Sender;
    }

    /**
     * @param OrderSender $Sender
     * @return $this
     */
    public function setSender(OrderSender $Sender)
    {
        $this->Sender = clone $Sender;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->RecipientName;
    }

    /**
     * @param string $RecipientName
     * @return $this
     */
    public function setRecipientName($RecipientName)
    {
        $this->RecipientName = $RecipientName;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->RecipientEmail;
    }

    /**
     * @param string $RecipientEmail
     * @return $this
     */
    public function setRecipientEmail($RecipientEmail)
    {
        $this->RecipientEmail = $RecipientEmail;
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
     * @return $this
     */
    public function setPhone($Phone)
    {
        $this->Phone = $Phone;
        return $this;
    }

    /**
     * @return int
     */
    public function getTariffTypeCode()
    {
        return $this->TariffTypeCode;
    }

    /**
     * @param int $TariffTypeCode
     * @return $this
     */
    public function setTariffTypeCode($TariffTypeCode)
    {
        $this->TariffTypeCode = $TariffTypeCode;
        return $this;
    }

    /**
     * @return float
     */
    public function getDeliveryRecipientCost()
    {
        return $this->DeliveryRecipientCost;
    }

    /**
     * @param float $DeliveryRecipientCost
     * @return $this
     */
    public function setDeliveryRecipientCost($DeliveryRecipientCost)
    {
        $this->DeliveryRecipientCost = $DeliveryRecipientCost;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryRecipientVATRate()
    {
        return $this->DeliveryRecipientVATRate;
    }

    /**
     * @param string $DeliveryRecipientVATRate
     * @return $this
     */
    public function setDeliveryRecipientVATRate($DeliveryRecipientVATRate)
    {
        $this->DeliveryRecipientVATRate = $DeliveryRecipientVATRate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryRecipientVATSum()
    {
        return $this->DeliveryRecipientVATSum;
    }

    /**
     * @param string $DeliveryRecipientVATSum
     * @return $this
     */
    public function setDeliveryRecipientVATSum($DeliveryRecipientVATSum)
    {
        $this->DeliveryRecipientVATSum = $DeliveryRecipientVATSum;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientCurrency()
    {
        return $this->RecipientCurrency;
    }

    /**
     * @param string $RecipientCurrency
     * @return $this
     */
    public function setRecipientCurrency($RecipientCurrency)
    {
        $this->RecipientCurrency = $RecipientCurrency;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemsCurrency()
    {
        return $this->ItemsCurrency;
    }

    /**
     * @param string $ItemsCurrency
     * @return $this
     */
    public function setItemsCurrency($ItemsCurrency)
    {
        $this->ItemsCurrency = $ItemsCurrency;
        return $this;
    }

    /**
     * @return string
     */
    public function getSellerName()
    {
        return $this->Seller['name'];
    }

    public function getSeller()
    {
        return $this->Seller;
    }

    /**
     * @param string $SellerName
     * @return $this
     */
    public function setSellerName($SellerName)
    {
        $this->Seller['name'] = $SellerName;
        return $this;
    }

    public function setSeller(array $seller)
    {
        $this->Seller = [
            'name'           => (string)Hash::get($seller, 'name'),
            'phone'          => (string)Hash::get($seller, 'phone'),
            'address'        => (string)Hash::get($seller, 'address'),
            'inn'            => (string)Hash::get($seller, 'inn'),
            'ownership_form' => Hash::get($seller, 'ownership_form')
        ];
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->Comment;
    }

    /**
     * @param string $Comment
     * @return $this
     */
    public function setComment($Comment)
    {
        $this->Comment = $Comment;
        return $this;
    }

    /**
     * @return OrderAddress
     */
    public function getAddress()
    {
        return $this->Address;
    }

    /**
     * @param OrderAddress $Address
     * @return $this
     */
    public function setAddress(OrderAddress $Address)
    {
        $this->Address = clone $Address;
        return $this;
    }

    /**
     * @return OrderPackageCollection
     */
    public function getPackage()
    {
        return $this->Package;
    }

    /**
     * @param OrderPackageCollection $Package
     * @return $this
     */
    public function setPackage(OrderPackageCollection $Package)
    {
        $this->Package = clone $Package;
        return $this;
    }

    /**
     * @return array
     */
    public function getAddService()
    {
        return $this->AddService;
    }

    /**
     * @param array $AddService
     * @return $this
     */
    public function setAddService($AddService)
    {
        $this->AddService = $AddService;
        return $this;
    }

    /**
     * @param ScheduleAttempt $schedule
     * @return $this
     */
    public function addSchedule(ScheduleAttempt $schedule)
    {
        if (!$this->Schedule) {
            $this->Schedule = new ScheduleAttemptCollection();
        }
        $this->Schedule->add($schedule);

        return $this;
    }

    /**
     * @return ScheduleAttemptCollection
     */
    public function getSchedule()
    {
        return $this->Schedule;
    }

    /**
     * @param ScheduleAttemptCollection $Schedule
     * @return $this
     */
    public function setSchedule(ScheduleAttemptCollection $Schedule)
    {
        $this->Schedule = clone $Schedule;
        return $this;
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $options = array_merge(['tag' => 'Order'], $options);
        $tag = Hash::get($options, 'tag');

        $arr = array(
            '@Number'         => $this->getNumber(),
            '@RecipientName'  => $this->getRecipientName(),
            '@Phone'          => $this->getPhone(),
            '@TariffTypeCode' => $this->getTariffTypeCode()
        );

        // sender city: code or postcode+optional country/name
        if ($this->getSendCityCode()) {
            $arr['@SendCityCode'] = $this->getSendCityCode();
        } else {
            $arr['@SendCityPostCode'] = $this->getSendCityPostCode();
            if ($this->getSendCountryCode()) {
                $arr['@SendCountryCode'] = $this->getSendCountryCode();
            }
            if ($this->getSendCityName()) {
                $arr['@SendCityName'] = $this->getSendCityName();
            }
        }

        // receiver city: code or postcode+optional country/name
        if ($this->getRecCityCode()) {
            $arr['@RecCityCode'] = $this->getRecCityCode();
        } else {
            $arr['@RecCityPostCode'] = $this->getRecCityPostCode();
            if ($this->getRecCountryCode()) {
                $arr['@RecCountryCode'] = $this->getRecCountryCode();
            }
            if ($this->getRecCityName()) {
                $arr['@RecCityName'] = $this->getRecCityName();
            }
        }

        if ($this->Sender && !$this->Sender->isEmpty()) {
            $arr['Sender'] = $this->Sender->toXmlArray(['tag' => null]);
        }

        if ($this->getSellerName()) {
            $arr['Seller'] = ['@Name'=>$this->Seller['name']];
            if($this->Seller['address']) $arr['Seller']['@Address']=$this->Seller['address'];
            if($this->Seller['phone']) $arr['Seller']['@Phone']=$this->Seller['phone'];
            if($this->Seller['address']) $arr['Seller']['@INN']=$this->Seller['inn'];
            if($this->Seller['address']) $arr['Seller']['@OwnershipForm']=$this->Seller['ownership_form'];
        }

        if ($this->getRecipientEmail()) {
            $arr['@RecipientEmail'] = $this->getRecipientEmail();
        }

        if ($this->getDeliveryRecipientCost()) {
            $arr['@DeliveryRecipientCost'] = $this->getDeliveryRecipientCost();

            if ($this->getDeliveryRecipientVATRate()) {
                $arr += array(
                    '@DeliveryRecipientVATRate' => $this->getDeliveryRecipientVATRate(),
                    '@DeliveryRecipientVATSum'  => $this->getDeliveryRecipientVATSum()
                );
            }
        }

        if ($this->getRecipientCurrency()) {
            $arr['@RecipientCurrency'] = $this->getRecipientCurrency();
        }

        if ($this->getItemsCurrency()) {
            $arr['@ItemsCurrency'] = $this->getItemsCurrency();
        }

        if ($this->getComment()) {
            $arr['@Comment'] = $this->getComment();
        }

        $arr['Address'] = $this->Address->toXmlArray(['tag' => null]);
        $arr['Package'] = $this->Package->toXmlArray(['tag' => null]);

        if ($this->getAddService()) {
            $arr['AddService'] = [];
            foreach ($this->getAddService() as $item) {
                $arr['AddService'][] = ['@ServiceCode' => $item];
            }
        }

        if ($this->getSchedule() && !$this->getSchedule()->isEmpty()) {
            $arr['Schedule'] = $this->getSchedule()->toXmlArray(['tag' => 'Attempt']);
        }

        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}