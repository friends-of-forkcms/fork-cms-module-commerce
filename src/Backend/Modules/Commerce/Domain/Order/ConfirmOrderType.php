<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class ConfirmOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // We want to have a clickable link to the terms-and-conditions page, and the easiest solution
        // for now is to replace a placeholder label with HTML in twig.
        $builder
            ->add('accept_terms_and_conditions', CheckboxType::class, [
                'label' => 'IAcceptTermsAndConditions', // Replace with translation in Twig
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
