<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018-2019
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use DateTimeInterface;
use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Hash;

class ScheduleAttempt implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var int */
    protected $ID = 1;

    /** @var DateTimeInterface */
    protected $Date;

    /** @var string */
    protected $Comment;

    /** @var string */
    protected $TimeBeg;

    /** @var string */
    protected $TimeEnd;

    /** @var string */
    protected $RecipientName;

    /** @var string */
    protected $Phone;

    /** @var StreetAddress */
    protected $Address;

    /**
     * ScheduleAttempt constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->Address = new StreetAddress();
        $this->Date = new \DateTimeImmutable();
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @param int $ID
     * @return ScheduleAttempt
     */
    public function setID($ID)
    {
        $this->ID = $ID;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * @param DateTimeInterface $Date
     * @return ScheduleAttempt
     */
    public function setDate($Date)
    {
        $this->Date = clone $Date;
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
     * @return ScheduleAttempt
     */
    public function setComment($Comment)
    {
        $this->Comment = $Comment;
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
     * @return ScheduleAttempt
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
     * @return ScheduleAttempt
     */
    public function setTimeEnd($TimeEnd)
    {
        $this->TimeEnd = $TimeEnd;
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
     * @return ScheduleAttempt
     */
    public function setRecipientName($RecipientName)
    {
        $this->RecipientName = $RecipientName;
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
     * @return ScheduleAttempt
     */
    public function setPhone($Phone)
    {
        $this->Phone = $Phone;
        return $this;
    }

    /**
     * @return StreetAddress
     */
    public function getAddress()
    {
        return $this->Address;
    }

    /**
     * @param StreetAddress $Address
     * @return ScheduleAttempt
     */
    public function setAddress(StreetAddress $Address = null)
    {
        if ($Address !== null) {
            $this->Address = clone $Address;

        } else {
            $this->Address = new StreetAddress();
        }
        return $this;
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $options = array_merge(['tag' => 'Schedule', 'address_tag' => 'Address'], $options);

        $arr = array(
            '@ID'   => $this->ID,
            '@Date' => $this->getDate()->format('Y-m-d')
        );
        foreach (['Comment', 'TimeBeg', 'TimeEnd', 'RecipientName', 'Phone'] as $item) {
            if ($this->$item) {
                $arr['@' . $item] = $this->$item;
            }
        }

        if (!$this->Address->isEmpty()) {
            $arr += $this->Address->toXmlArray(['tag' => Hash::get($options, 'address_tag')]);
        }

        $tag = Hash::get($options, 'tag');
        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}