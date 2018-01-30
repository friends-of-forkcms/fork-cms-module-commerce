<?php

namespace Backend\Modules\Catalog\Domain\ProductOption;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionType extends AbstractType
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
            'required',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'lbl.Required',
            ]
        )->add(
            'placeholder',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.Placeholder',
            ]
        )->add(
            'type',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'lbl.Type',
                'placeholder' => 'lbl.MakeAChoice',
                'choices' => [
                    'lbl.DropDown' => ProductOption::DISPLAY_TYPE_DROP_DOWN,
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductOptionDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product_option';
    }
}
