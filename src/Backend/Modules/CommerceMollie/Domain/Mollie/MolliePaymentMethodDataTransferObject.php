<?php

namespace Backend\Modules\CommerceMollie\Domain\Mollie;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodDataTransferObject;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class MolliePaymentMethodDataTransferObject extends PaymentMethodDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $apiKey = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $orderInitId = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $orderCompletedId = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $orderCancelledId = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $orderRefundedId = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $orderExpiredId = null;

    public $paymentMethods = [];

    public function __construct(PaymentMethod $paymentMethod = null, Locale $locale)
    {
        $this->name = 'Mollie'; // default name filled in the UI
        $this->module = 'CommerceMollie';
        parent::__construct($paymentMethod, $locale);
    }

    public function __set($key, $value)
    {
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
