<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Calculator\Request;

use DateTimeImmutable;
use Exception;
use JsonSerializable;
use SergeR\Webasyst\CdekSDK\Utils\AuthorizedRequest;
use SergeR\Webasyst\CdekSDK\Type\CdekService;
use SergeR\CakeUtility\Hash;

/**
 * Class CalculationRequest
 * @package SergeR\Webasyst\CdekSDK\API\Order\Request
 */
class CalculationRequest implements AuthorizedRequest, JsonSerializable
{
    /**
     * @var string|null
     */
    protected $account;

    /**
     * @var string|null
     */
    protected $password;

    /** @var DateTimeImmutable */
    protected $dateExecute;

    protected $senderCityId;

    protected $senderCityPostCode;

    protected $receiverCityId;

    protected $receiverCityPostCode;

    protected $tariff;

    /** @var int */
    protected $modeId;

    /** @var array */
    protected $goods = [];

    /** @var array */
    protected $services = [];

    /**
     * CalculationRequest constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateExecute = new DateTimeImmutable;
    }

    /**
     * @param string|null $account
     * @param string|null $secure
     * @return void
     */
    public function setCredentials($account, $secure)
    {
        $this->account = $account;
        $this->password = $secure;
    }

    /**
     * @throws Exception
     */
    public function toArray()
    {
        if (!$this->getSenderCityId() && !$this->getSenderCityPostCode()) {
            throw new Exception('senderCityId or senderCityPostCode required');
        }

        if (!$this->getReceiverCityId() && !$this->getReceiverCityPostCode()) {
            throw new Exception('receiverCityId or receiverCityPostCode required');
        }

        if (!$this->tariff) {
            throw new Exception('At least one tariff must be set');
        }

        $date = $this->getFormattedDate();

        $arr = [
            'version' => '1.0',
        ];

        if ($this->account && $this->password) {
            $arr += ['authLogin' => $this->account, 'secure' => md5($date . '&' . $this->password)];
        }
        $arr += ['dateExecute' => $date];

        if ($this->getSenderCityId()) {
            $arr['senderCityId'] = $this->getSenderCityId();
        } else {
            $arr['senderCityPostCode'] = $this->getSenderCityPostCode();
        }

        if ($this->getReceiverCityId()) {
            $arr['receiverCityId'] = $this->getReceiverCityId();
        } else {
            $arr['receiverCityPostCode'] = $this->getReceiverCityPostCode();
        }

        if (!is_array($this->tariff)) {
            $arr['tariffId'] = $this->tariff;
        } elseif (count($this->tariff) === 1) {
            $t = reset($this->tariff);
            $arr['tariffId'] = $t;
        } else {
            $priority = 1;
            $arr['tariffList'] = [];
            foreach ($this->tariff as $t) {
                $arr['tariffList'][] = ['priority' => $priority++, 'id' => $t];
            }
        }

        if($this->getModeId() !== null) {
            $arr['modeId'] = $this->getModeId();
        }

        $arr['goods'] = [];
        foreach ($this->goods as $good) {
            $weight = Hash::get($good, 'weight');
            $length = Hash::get($good, 'length');
            $width = Hash::get($good, 'width');
            $height = Hash::get($good, 'height');
            $volume = Hash::get($good, 'volume');

            if (!$weight) {
                throw new Exception('Weight required');
            }

            if ((!$length || !$width || !$height) && !$volume) {
                throw new Exception('Required width, length and height or volume');
            }

            $_g = ['weight' => $weight];
            if ($length && $width && $height) {
                $_g += ['length' => $length, 'width' => $width, 'height' => $height];
            } else {
                $_g['volume'] = $volume;
            }

            $arr['goods'][] = $_g;
        }

        if ($this->services) {
            $arr['services'] = [];
            foreach ($this->services as $service) {
                if (is_array($service)) {
                    $_s = $service;
                } elseif ($service instanceof CdekService) {
                    $_s = array('id' => $service->getCode());
                    if ($service->isCalcRequiresParam()) {
                        $_s['param'] = $service->getParam();
                    }
                } elseif (($service = (int)($service))) {
                    $_s = array('id' => $service);
                } else {
                    continue;
                }
                $arr['services'][] = $_s;
            }
        }

        return $arr;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateExecute()
    {
        return $this->dateExecute;
    }

    /**
     * @param DateTimeImmutable $dateExecute
     * @return $this
     */
    public function setDateExecute($dateExecute)
    {
        $this->dateExecute = $dateExecute;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormattedDate()
    {
        return $this->getDateExecute()->format('Y-m-d');
    }

    /**
     * @return mixed
     */
    public function getSenderCityId()
    {
        return $this->senderCityId;
    }

    /**
     * @param int|null $senderCityId
     * @return $this
     */
    public function setSenderCityId($senderCityId)
    {
        $this->senderCityId = ($senderCityId === null ? null : (int)$senderCityId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSenderCityPostCode()
    {
        return $this->senderCityPostCode;
    }

    /**
     * @param string|null $senderCityPostCode
     * @return CalculationRequest
     */
    public function setSenderCityPostCode($senderCityPostCode)
    {
        $this->senderCityPostCode = ($senderCityPostCode === null ? null : (string)$senderCityPostCode);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReceiverCityId()
    {
        return $this->receiverCityId;
    }

    /**
     * @param int|null $receiverCityId
     * @return $this
     */
    public function setReceiverCityId($receiverCityId)
    {
        $this->receiverCityId = ($receiverCityId === null ? $receiverCityId : (int)$receiverCityId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiverCityPostCode()
    {
        return $this->receiverCityPostCode;
    }

    /**
     * @param string|null $receiverCityPostCode
     */
    public function setReceiverCityPostCode($receiverCityPostCode)
    {
        $this->receiverCityPostCode = ($receiverCityPostCode === null ? null : (string)$receiverCityPostCode);
    }

    /**
     * @return mixed
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param int|array $tariff
     * @return $this
     */
    public function setTariff($tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * @return array
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param array $goods
     * @return $this
     */
    public function setGoods($goods)
    {
        $this->goods = $goods;
        return $this;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param array $services
     * @return CalculationRequest
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @return int
     */
    public function getModeId()
    {
        return $this->modeId;
    }

    /**
     * @param int $modeId
     * @return CalculationRequest
     */
    public function setModeId($modeId)
    {
        $this->modeId = $modeId;
        return $this;
    }

    /**
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @throws Exception
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}