<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Form\Type\MetaType;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistoryDataTransferObject;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Common\Form\ImageType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'orderStatus',
            EntityType::class,
            [
                'required' => true,
                'label'    => 'lbl.OrderStatus',
                'class'    => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                              ->orderBy('i.title', 'ASC');
                },
                'choice_label'  => 'title'
            ]
        )->add(
            'message',
            TextareaType::class,
            [
                'required'    => false,
                'label'       => 'lbl.Comment',
            ]
        )->add(
            'notify',
            CheckboxType::class,
            [
                'required'     => false,
                'label'        => 'lbl.NotifyCustomer',
            ]
        );
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
