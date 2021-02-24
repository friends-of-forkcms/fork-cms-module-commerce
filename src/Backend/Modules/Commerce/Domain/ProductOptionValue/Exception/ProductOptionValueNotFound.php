<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Exception;

use Exception;

class ProductOptionValueNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the product option value you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a product option value with id = "'.$id.'".');
    }
}
