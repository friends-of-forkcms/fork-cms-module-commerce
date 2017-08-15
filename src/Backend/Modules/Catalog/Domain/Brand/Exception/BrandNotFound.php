<?php

namespace Backend\Modules\Catalog\Domain\Brand\Exception;

use Exception;

class BrandNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a Brand with id = "' . $id . '".');
    }
}
