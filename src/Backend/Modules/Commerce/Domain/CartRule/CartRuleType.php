<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Symfony\Component\Form\AbstractType;
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
            DateType::class,
            [
                'required' => true,
                'label' => 'lbl.From',
            ]
        )->add(
            'till',
            DateType::class,
            [
                'required' => false,
                'label' => 'lbl.Till',
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
            'minimum_amount',
            MoneyType::class,
            [
                'required' => false,
                'label' => 'lbl.MinimumAmount',
            ]
        )->add(
            'reduction_percentage',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.ReductionPercentage',
            ]
        )->add(
            'reduction_amount',
            MoneyType::class,
            [
                'required' => false,
                'label' => 'lbl.ReductionAmount',
            ]
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
