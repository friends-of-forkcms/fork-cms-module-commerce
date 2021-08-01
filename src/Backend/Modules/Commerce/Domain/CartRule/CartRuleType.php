<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Backend\Modules\Commerce\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.Title',
            ]
        )->add(
            'from',
            DateTimeType::class,
            [
                'required' => true,
                'label' => 'lbl.From',
                'date_widget' => 'single_text',
                'html5' => true,
            ]
        )->add(
            'till',
            DateTimeType::class,
            [
                'required' => false,
                'label' => 'lbl.Till',
                'date_widget' => 'single_text',
                'html5' => true,
            ]
        )->add(
            'quantity',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.Quantity',
                'scale' => 0,
            ]
        )->add(
            'code',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.DiscountCode',
            ]
        )->add(
            $builder
                ->create(
                    'minimum_price',
                    MoneyType::class,
                    [
                        'required' => false,
                        'label' => 'lbl.MinimumAmount',
                    ]
                )
                ->addModelTransformer(new MoneyToLocalizedStringTransformer())
        )->add(
            'reduction_percentage',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.ReductionPercentage',
            ]
        )->add(
            $builder
                ->create(
                    'reduction_price',
                    MoneyType::class,
                    [
                        'required' => false,
                        'label' => 'lbl.ReductionAmount',
                    ]
                )
                ->addModelTransformer(new MoneyToLocalizedStringTransformer())
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CartRuleDataTransferObject::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'cart_rule';
    }
}
