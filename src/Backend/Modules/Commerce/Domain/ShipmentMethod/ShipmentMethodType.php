<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ShipmentMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'isEnabled',
                ChoiceType::class,
                [
                    'required' => false,
                    'label' => 'lbl.Installed',
                    'placeholder' => false,
                    'choices' => [
                        'lbl.Yes' => true,
                        'lbl.No' => false,
                    ],
                ]
            )
            ->add(
                'availablePaymentMethods',
                EntityType::class,
                [
                    'required' => false,
                    'label' => 'lbl.AvailablePaymentMethods',
                    'class' => PaymentMethod::class,
                    'choice_value' => 'id',
                    'choice_label' => 'name',
                    'expanded' => true,
                    'multiple' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entityManager' => '',
            ]
        );
    }
}
