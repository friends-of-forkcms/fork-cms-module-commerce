<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'order',
            EntityType::class,
            [
                'required' => false,
                'label' => 'lbl.Order',
                'placeholder' => 'lbl.None',
                'class' => Order::class,
                'choices' => $options['categories'],
                'choice_label' => function ($order) {
                    $prefix = null;
                    if ($order->path > 0) {
                        $prefix = str_repeat('-', $order->path) . ' ';
                    }

                    return $prefix . $order->getTitle();
                },
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'categories' => null,
            ]
        );
    }
}
