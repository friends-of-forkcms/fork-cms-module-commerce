<?php

namespace Backend\Modules\CommerceMollie\Domain\Payment;

class MolliePaymentDataTransferObject
{
    protected ?MolliePayment $paymentEntity = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $order_id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $method;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $transaction_id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $bank_account = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $bank_status = null;


    public function __construct(MolliePayment $payment = null)
    {
        $this->paymentEntity = $payment;

        if (!$this->hasExistingPayment()) {
            return;
        }

        $this->order_id = $this->paymentEntity->getOrderId();
        $this->method = $this->paymentEntity->getMethod();
        $this->transaction_id = $this->paymentEntity->getTransactionId();
        $this->bank_account = $this->paymentEntity->getBankAccount();
        $this->bank_status = $this->paymentEntity->getBankStatus();
    }

    public function getPaymentEntity(): MolliePayment
    {
        return $this->paymentEntity;
    }

    public function hasExistingPayment(): bool
    {
        return $this->paymentEntity instanceof MolliePayment;
    }
}
