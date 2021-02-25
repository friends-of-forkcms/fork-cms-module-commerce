<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Symfony\Component\Form\FormInterface;

class ValidationGroupResolver
{
    public function __invoke(FormInterface $form)
    {
        $groups = [];
        $groups[] = 'Default';

        if ($form->getData()->type == Product::TYPE_DIMENSIONS) {
            $groups[] = 'dimensions';
        }

        return $groups;
    }
}
