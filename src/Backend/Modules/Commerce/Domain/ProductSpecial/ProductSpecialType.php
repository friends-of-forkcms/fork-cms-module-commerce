<?php

namespace Backend\Modules\Commerce\Domain\ProductSpecial;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tbbc\MoneyBundle\Form\Type\MoneyType;

class ProductSpecialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', MoneyType::class, [
                'required' => true,
                'label' => 'lbl.Price',
            ])
            ->add('start_date', DateTimeType::class, [
                'required' => true,
                'label' => 'lbl.StartDate',
            ])
            ->add('end_date', DateTimeType::class, [
                'required' => false,
                'label' => 'lbl.EndDate',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductSpecial::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product_specials';
    }
}
