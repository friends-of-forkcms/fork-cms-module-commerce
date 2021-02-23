<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutShipmentMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Parse the shipment methods
        $shipmentMethods = [];
        foreach ($options['shipment_methods'] as $key => $shipmentMethod) {
            $shipmentMethods[$shipmentMethod['label']] = $key;
        }

        $builder->add(
            'shipment_method',
            ChoiceType::class,
            [
                'choices' => $shipmentMethods,
                'expanded' => true
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => CheckoutShipmentMethodDataTransferObject::class,
                'shipment_methods' => []
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'checkout_shipment_method';
    }
}
