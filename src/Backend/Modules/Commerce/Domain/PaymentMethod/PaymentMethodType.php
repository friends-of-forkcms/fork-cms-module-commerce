<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class PaymentMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isEnabled', ChoiceType::class, [
                'required' => false,
                'label' => 'lbl.Enabled',
                'placeholder' => false,
                'choices' => [
                    'lbl.Yes' => true,
                    'lbl.No' => false,
                ],
            ]);
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
