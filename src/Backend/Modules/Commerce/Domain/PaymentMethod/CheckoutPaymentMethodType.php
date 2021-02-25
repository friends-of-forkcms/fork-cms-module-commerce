<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutPaymentMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new PaymentMethodSubscriber());

        // Parse the payment methods
        $paymentMethods = [];
        foreach($options['payment_methods'] as $key => $paymentMethod) {
            $paymentMethods[$paymentMethod['label']] = $key;
        }

        $builder->add(
            'payment_method',
            ChoiceType::class,
            [
                'choices' => $paymentMethods,
                'expanded' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => CheckoutPaymentMethodDataTransferObject::class,
                'payment_methods' => [],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'checkout_payment_method';
    }
}
