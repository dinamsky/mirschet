<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018-2019
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;

class StatusReportOrder
{
    /** @var string */
    protected $ActNumber;

    /** @var string */
    protected $Number;

    /** @var string */
    protected $DispatchNumber;

    /** @var \DateTimeImmutable|null */
    protected $DeliveryDate;

    /** @var string|null */
    protected $RecipientName;

    /** @var string|null */
    protected $ReturnDispatchNumber;

    /** @var StatusReportStatus */
    protected $Status;

    /** @var CommonStatusReportState|null */
    protected $Reason;

    /** @var StatusReportDelayReasonState|null */
    protected $DelayReason;

    /** @var StatusReportPackageCollection|null */
    protected $Package;

    /** @var StatusReportAttemptCollection|null */
    protected $Attempt;

    /** @var StatusReportCalls */
    protected $Call;

    /** @var StatusReportOrder|null */
    protected $ReturnOrder;

    public function __construct()
    {
        $this->Status = new StatusReportStatus;
        $this->Reason = new CommonStatusReportState();
        $this->DelayReason = new StatusReportDelayReasonState();
    }

    /**
     * @return string
     */
    public function getActNumber()
    {
        return $this->ActNumber;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * @return string
     */
    public function getDispatchNumber()
    {
        return $this->DispatchNumber;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeliveryDate()
    {
        return $this->DeliveryDate;
    }

    /**
     * @return string|null
     */
    public function getRecipientName()
    {
        return $this->RecipientName;
    }

    /**
     * @return string|null
     */
    public function getReturnDispatchNumber()
    {
        return $this->ReturnDispatchNumber;
    }

    /**
     * @return StatusReportStatus
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @return CommonStatusReportState|null
     */
    public function getReason()
    {
        return $this->Reason;
    }

    /**
     * @return StatusReportDelayReasonState|null
     */
    public function getDelayReason()
    {
        return $this->DelayReason;
    }

    /**
     * @return StatusReportPackageCollection|null
     */
    public function getPackage()
    {
        return $this->Package;
    }

    /**
     * @return StatusReportAttemptCollection|null
     */
    public function getAttempt()
    {
        return $this->Attempt;
    }

    /**
     * @return StatusReportCalls
     */
    public function getCall()
    {
        return $this->Call;
    }

    /**
     * @param array $xml_array
     * @return StatusReportOrder
     * @throws \Exception
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $self->ActNumber = Hash::get($xml_array, '@ActNumber');
        $self->Number = Hash::get($xml_array, '@Number');
        $self->DispatchNumber = Hash::get($xml_array, '@DispatchNumber');
        if (($delivery_date = Hash::get($xml_array, '@DeliveryDate'))) {
            $self->DeliveryDate = new \DateTimeImmutable($delivery_date);
        }
        $self->RecipientName = (string)Hash::get($xml_array, '@RecipientName');
        if ($return_dn = Hash::get($xml_array, '@ReturnDispatchNumber')) {
            $self->ReturnDispatchNumber = (string)$return_dn;
        }

        $self->Status = StatusReportStatus::fromArray((array)Hash::get($xml_array, 'Status'));
        if (($reason = Hash::get($xml_array, 'Reason'))) {
            if (!empty($reason['@Date'])) {
                $self->Reason = CommonStatusReportState::fromArray((array)$reason);
            }
        }
        if (($delay_reason = Hash::get($xml_array, 'DelayReason'))) {
            if (!empty($delay_reason['@Date'])) {
                $self->DelayReason = StatusReportDelayReasonState::fromArray((array)$delay_reason);
            }
        }
        if ($package = Hash::get($xml_array, 'Package')) {
            $self->Package = StatusReportPackageCollection::fromArray($package);
        }
        if ($calls = Hash::get($xml_array, 'Call')) {
            $self->Call = StatusReportCalls::fromArray($calls);
        }

        if (($ro = Hash::get($xml_array, 'ReturnOrder'))) {
            $self->ReturnOrder = StatusReportOrder::fromArray($ro);
        }

        return $self;
    }
}