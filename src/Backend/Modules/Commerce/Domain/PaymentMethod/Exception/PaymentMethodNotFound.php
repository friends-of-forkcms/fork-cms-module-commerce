<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod\Exception;

use Exception;

class PaymentMethodNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the payment method you have given is null');
    }

    public static function forId(int $id): self
    {
        return new self('Can\'t find a PaymentMethod with id = "' . $id . '".');
    }
}
