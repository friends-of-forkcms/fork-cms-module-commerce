<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfirmOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accept_terms_and_conditions', HiddenType::class, [
                'required' => true,
                'label' => 'lbl.IAcceptTermsAndConditions',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ConfirmOrderDataTransferObject::class,
                'payment_methods' => [],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'checkout_confirm_order';
    }
}
