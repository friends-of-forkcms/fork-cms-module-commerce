<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OneOrMoreFilled extends Constraint
{
    public string $message = 'Dit veld of de velden "{{ string }}" moeten gevuld zijn.';

    public array $fields = [];

    public function getRequiredOptions()
    {
        return ['fields'];
    }
}
