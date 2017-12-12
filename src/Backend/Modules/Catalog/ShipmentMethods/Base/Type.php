<?php

namespace Backend\Modules\Catalog\ShipmentMethods\Base;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

abstract class Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'installed',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => 'lbl.Installed',
                'choices' => [
                    'lbl.Yes' => true,
                    'lbl.No' => false
                ]
            ]
        );
    }
}
