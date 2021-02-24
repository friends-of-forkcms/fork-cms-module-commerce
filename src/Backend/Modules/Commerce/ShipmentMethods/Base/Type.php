<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Base;

use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'installed',
            ChoiceType::class,
            [
                'required' => false,
                'label' => 'lbl.Installed',
                'choices' => [
                    'lbl.Yes' => true,
                    'lbl.No' => false,
                ],
            ]
        )->add(
            'available_payment_methods',
            EntityType::class,
            [
                'required' => false,
                'label' => 'lbl.AvailablePaymentMethods',
                'class' => PaymentMethod::class,
                'choice_value' => 'name',
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ]
        );
    }
}
