<?php

namespace Backend\Modules\Catalog\PaymentMethods\CashOnDelivery;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusTransformer;
use Backend\Modules\Catalog\PaymentMethods\Base\Type;
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
                'label'    => 'lbl.Name'
            ]
        )->add(
            'orderInitId',
            EntityType::class,
            [
                'required'      => true,
                'label'         => 'lbl.OrderInitialized',
                'class'         => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                              ->orderBy('i.title', 'ASC');
                },
                'choice_label'  => 'title'
            ]
        );

        $builder->get('orderInitId')->addModelTransformer(new OrderStatusTransformer($options['entityManager']));
    }
}
