<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Constraints;

use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class OneOrMoreFilledValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof OneOrMoreFilled) {
            throw new UnexpectedTypeException($constraint, OneOrMoreFilled::class);
        }

        /**
         * @var Form $form
         */
        $form = $this->context->getRoot();

        $isValid = $this->isFilled($value);
        $fieldLabels = [
//            $this->
        ];

        foreach ($constraint->fields as $field) {
            $formField = $form->get($field);

            if (!$isValid && $this->isFilled($formField->getData())) {
                $isValid = true;
            }

            $fieldLabels[] = $formField->getConfig()->getOption('label');
        }

        if (!$isValid) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ fields }}', implode(', ', $fieldLabels))
            ->addViolation();
        }
    }

    private function isFilled($value): bool
    {
        return $value != null && $value != '';
    }
}
