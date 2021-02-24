<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod\Exception;

use Exception;

class PaymentMethodNotFound extends Exception
{
    public static function forEmptyName(): self
    {
        return new self('The name you have given is null');
    }

    public static function forName(string $name): self
    {
        return new self('Can\'t find a PaymentMethod with name = "'.$name.'".');
    }
}
