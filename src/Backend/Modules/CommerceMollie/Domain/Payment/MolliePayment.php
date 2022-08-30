<?php

namespace Backend\Modules\CommerceMollie\Domain\Payment;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_orders_mollie_payments")
 * @ORM\Entity(repositoryClass="MolliePaymentRepository")
 */
class MolliePayment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public int $order_id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private string $method;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private string $transaction_id;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private ?string $bank_account;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private ?string $bank_status;

    public function __construct(
        int $order_id,
        string $method,
        string $transaction_id,
        ?string $bank_account,
        ?string $bank_status
    ) {
        $this->order_id = $order_id;
        $this->method = $method;
        $this->transaction_id = $transaction_id;
        $this->bank_account = $bank_account;
        $this->bank_status = $bank_status;
    }


    public static function fromDataTransferObject(MolliePaymentDataTransferObject $dataTransferObject): MolliePayment
    {
        if ($dataTransferObject->hasExistingPayment()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(MolliePaymentDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->order_id,
            $dataTransferObject->method,
            $dataTransferObject->transaction_id,
            $dataTransferObject->bank_account,
            $dataTransferObject->bank_status
        );
    }

    private static function update(MolliePaymentDataTransferObject $dataTransferObject): self
    {
        $payment = $dataTransferObject->getPaymentEntity();

        $payment->order_id = $dataTransferObject->order_id;
        $payment->method = $dataTransferObject->method;
        $payment->transaction_id = $dataTransferObject->transaction_id;
        $payment->bank_account = $dataTransferObject->bank_account;
        $payment->bank_status = $dataTransferObject->bank_status;

        return $payment;
    }

    public function getOrderId(): int
    {
        return $this->order_id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getTransactionId(): string
    {
        return $this->transaction_id;
    }

    public function getBankAccount(): ?string
    {
        return $this->bank_account;
    }

    public function getBankStatus(): ?string
    {
        return $this->bank_status;
    }
}
