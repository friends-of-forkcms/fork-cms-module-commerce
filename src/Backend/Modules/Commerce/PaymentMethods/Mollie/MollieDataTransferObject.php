<?php

namespace Backend\Modules\Commerce\PaymentMethods\Mollie;

use Backend\Modules\Commerce\PaymentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class MollieDataTransferObject extends DataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $apiKey;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderInitId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderCompletedId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderCancelledId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderRefundedId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderExpiredId;

    public array $paymentMethods = [];

    public function __construct()
    {
        $this->name = 'Mollie';
    }

    public function __set($key, $value)
    {
        $this->paymentMethods[$key] = $value;
    }

    public function __get($key)
    {
        return $this->paymentMethods[$key] ?? null;
    }
}
