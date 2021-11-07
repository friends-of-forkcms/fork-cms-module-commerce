<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod\Exception;

use Exception;

class ShipmentMethodNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the shipment method you have given is null');
    }

    public static function forId(int $id): self
    {
        return new self('Can\'t find a ShipmentMethod with id = "' . $id . '".');
    }
}
