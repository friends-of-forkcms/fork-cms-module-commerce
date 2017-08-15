<?php

namespace Backend\Modules\Catalog\Domain\StockStatus\Exception;

use Exception;

class StockStatusNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a StockStatus with id = "' . $id . '".');
    }
}
