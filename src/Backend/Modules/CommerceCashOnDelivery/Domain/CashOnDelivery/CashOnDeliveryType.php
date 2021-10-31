<?php

namespace Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery;

use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusTransformer;
use Backend\Modules\Commerce\PaymentMethods\Base\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CashOnDeliveryType extends Type
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.Name',
            ]
        )->add(
            'orderInitId',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.OrderInitialized',
                'class' => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                              ->orderBy('i.title', 'ASC');
                },
                'choice_label' => 'title',
            ]
        );

        $builder
            ->get('orderInitId')
            ->addModelTransformer(new OrderStatusTransformer($options['entityManager']));
    }
}
