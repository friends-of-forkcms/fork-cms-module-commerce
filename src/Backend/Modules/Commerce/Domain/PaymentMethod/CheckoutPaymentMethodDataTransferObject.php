<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Symfony\Component\Validator\Constraints as Assert;

class CheckoutPaymentMethodDataTransferObject
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $payment_method;

    private $data = [];

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->data)) {
            return null;
        }

        return $this->data[$name];
    }
}
