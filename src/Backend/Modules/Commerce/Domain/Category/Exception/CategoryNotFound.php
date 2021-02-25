<?php

namespace Backend\Modules\Commerce\Domain\Category\Exception;

use Exception;

class CategoryNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the category you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a category with id = "' . $id . '".');
    }
}
