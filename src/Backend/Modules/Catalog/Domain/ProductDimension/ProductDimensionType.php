<?php

namespace Backend\Modules\Catalog\Domain\ProductDimension;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDimensionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'price',
            MoneyType::class,
            [
                'required' => true,
                'label' => 'lbl.Price',
            ]
        )->add(
            'width',
            HiddenType::class,
            [
                'required' => true,
                'label' => 'lbl.Width',
            ]
        )->add(
            'height',
            HiddenType::class,
            [
                'required' => true,
                'label' => 'lbl.Height',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductDimension::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product_combinations';
    }
}
