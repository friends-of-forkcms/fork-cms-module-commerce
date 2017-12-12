<?php

namespace Backend\Modules\Catalog\Domain\ShipmentMethod\Exception;

use Exception;

class ShipmentMethodNotFound extends Exception
{
    public static function forEmptyName(): self
    {
        return new self('The name you have given is null');
    }

    public static function forName(string $name): self
    {
        return new self('Can\'t find a ShipmentMethod with name = "' . $name . '".');
    }
}
