<?php

namespace Backend\Modules\CommerceMollie\Domain\Payment\Command;

use Backend\Modules\CommerceMollie\Domain\Payment\MolliePayment;
use Backend\Modules\CommerceMollie\Domain\Payment\MolliePaymentDataTransferObject;

class CreateMolliePayment extends MolliePaymentDataTransferObject
{
    public function setPaymentEntity(MolliePayment $payment): void
    {
        $this->paymentEntity = $payment;
    }
}
