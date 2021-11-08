<?php

namespace Backend\Modules\Commerce\Domain\ProductSpecial;

use Backend\Modules\Commerce\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSpecialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create(
                    'price',
                    MoneyType::class,
                    [
                        'required' => true,
                        'label' => 'lbl.Price',
                    ]
                )
                ->addModelTransformer(new MoneyToLocalizedStringTransformer())
            )
            ->add('start_date', DateType::class, [
                'required' => true,
                'label' => 'lbl.StartDate',
            ])
            ->add('end_date', DateType::class, [
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
