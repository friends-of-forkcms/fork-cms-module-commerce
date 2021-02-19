<?php

namespace Backend\Modules\Catalog\PaymentMethods\Mollie;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\PaymentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class MollieDataTransferObject extends DataTransferObject
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $apiKey;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderInitId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderCompletedId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderCancelledId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderRefundedId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderExpiredId;

    public $paymentMethods = [];

    public function __set($key, $value) {
        $this->paymentMethods[$key] = $value;
    }

    public function __get($key)
    {
        if (!isset($this->paymentMethods[$key])) {
            return null;
        }

        return $this->paymentMethods[$key];
    }
}
