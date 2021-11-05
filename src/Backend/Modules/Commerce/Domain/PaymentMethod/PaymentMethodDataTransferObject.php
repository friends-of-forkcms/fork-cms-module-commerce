<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Symfony\Component\Validator\Constraints as Assert;

abstract class PaymentMethodDataTransferObject
{
    protected ?PaymentMethod $paymentMethod = null;

    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name;

    public string $module;

    public bool $isEnabled = false;

    public Locale $locale;

    public function __construct(PaymentMethod $paymentMethod = null, Locale $locale)
    {
        $this->locale = $locale;
        $this->paymentMethod = $paymentMethod;

        if (!$this->hasExistingPaymentMethod()) {
            return;
        }

        $this->id = $this->paymentMethod->getId();
        $this->name = $this->paymentMethod->getName();
        $this->module = $this->paymentMethod->getModule();
        $this->isEnabled = $this->paymentMethod->isEnabled();
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function hasExistingPaymentMethod(): bool
    {
        return $this->paymentMethod instanceof PaymentMethod;
    }
}
