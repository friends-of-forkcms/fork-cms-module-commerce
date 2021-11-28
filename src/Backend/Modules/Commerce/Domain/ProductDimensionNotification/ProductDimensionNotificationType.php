<?php

namespace Backend\Modules\Commerce\Domain\ProductDimensionNotification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDimensionNotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('width', NumberType::class, [
                'required' => true,
                'label' => 'lbl.Width',
            ])
            ->add('height', NumberType::class, [
                'required' => true,
                'label' => 'lbl.Height',
            ])
            ->add('message', TextareaType::class, [
                'required' => false,
                'label' => 'lbl.Notification',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductDimensionNotification::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product_dimension_notification';
    }
}
