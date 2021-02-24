<?php

namespace Backend\Modules\Commerce\ShipmentMethods\BasedOnWeight;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.Name',
            ]
        )->add(
            'price',
            MoneyType::class,
            [
                'required' => true,
                'label' => 'lbl.Price',
            ]
        )->add(
            'tillWeight',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.TillWeight',
            ]
        )->add(
            'fromWeight',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.FromWeight',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ValueDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'values';
    }
}
