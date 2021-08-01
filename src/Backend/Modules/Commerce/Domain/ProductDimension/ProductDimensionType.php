<?php

namespace Backend\Modules\Commerce\Domain\ProductDimension;

use Backend\Modules\Commerce\Form\DataTransformer\MoneyToLocalizedStringTransformer;
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
            $builder->create(
                'price',
                MoneyType::class,
                [
                    'required' => true,
                    'label' => 'lbl.Price',
                ]
            )
            ->addModelTransformer(new MoneyToLocalizedStringTransformer())
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
