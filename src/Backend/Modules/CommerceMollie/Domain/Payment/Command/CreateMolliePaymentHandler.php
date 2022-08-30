<?php

namespace Backend\Modules\CommerceMollie\Domain\Payment\Command;

use Backend\Modules\CommerceMollie\Domain\Payment\MolliePayment;
use Backend\Modules\CommerceMollie\Domain\Payment\MolliePaymentRepository;

final class CreateMolliePaymentHandler
{
    /** @var MolliePaymentRepository */
    private MolliePaymentRepository $paymentRepository;

    public function __construct(MolliePaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function handle(CreateMolliePayment $createPayment): void
    {
        $payment = MolliePayment::fromDataTransferObject($createPayment);
        $this->paymentRepository->add($payment);

        $createPayment->setPaymentEntity($payment);
    }
}
