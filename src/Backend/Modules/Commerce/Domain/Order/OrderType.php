<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistoryDataTransferObject;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderStatus', EntityType::class, [
                'required' => true,
                'label' => 'lbl.OrderStatus',
                'class' => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.title', 'ASC');
                },
                'choice_label' => 'title',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => OrderHistoryDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'order';
    }
}
