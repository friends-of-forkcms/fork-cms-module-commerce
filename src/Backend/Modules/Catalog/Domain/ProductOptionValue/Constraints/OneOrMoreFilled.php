<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OneOrMoreFilled extends Constraint
{
    public $message = 'Dit veld of de velden "{{ string }}" moeten gevuld zijn.';

    public $fields = [];

    public function getRequiredOptions()
    {
        return ['fields'];
    }
}
