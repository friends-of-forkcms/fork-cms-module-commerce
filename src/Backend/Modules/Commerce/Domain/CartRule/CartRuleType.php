<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Backend\Modules\Commerce\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Money\Money;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'lbl.Title',
            ])
            ->add('from', DateTimeType::class, [
                'required' => true,
                'label' => 'lbl.StartDate',
                'date_widget' => 'single_text',
                'html5' => false,
                'date_format' => 'dd/MM/yyyy', // For consistency with the data-mask and JS datepicker
            ])
            ->add('till', DateTimeType::class, [
                'required' => false,
                'label' => 'lbl.EndDate',
                'date_widget' => 'single_text',
                'html5' => false,
                'date_format' => 'dd/MM/yyyy', // For consistency with the data-mask and JS datepicker
            ])
            ->add('quantity', NumberType::class, [
                'required' => true,
                'label' => 'lbl.DiscountQuantity',
                'scale' => 0,
            ])
            ->add('code', TextType::class, [
                'required' => false,
                'label' => 'lbl.DiscountCode',
            ])
            ->add(
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
            )
            ->add('reduction_percentage', PercentType::class, [
                'required' => false,
                'label' => 'lbl.ReductionPercentage',
            ])
            ->add(
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
                    ->addModelTransformer(new CallbackTransformer(
                        // Store an 0 euro object as NULL in the DTO
                        fn ($value): ?string => $value,
                        fn ($value): ?Money => $value instanceof Money && $value->isZero() ? null : $value
                    ))
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
