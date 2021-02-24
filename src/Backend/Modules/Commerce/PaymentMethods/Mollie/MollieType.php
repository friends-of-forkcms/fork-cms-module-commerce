<?php

namespace Backend\Modules\Commerce\PaymentMethods\Mollie;

use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusTransformer;
use Backend\Modules\Commerce\PaymentMethods\Base\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MollieType extends Type
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
            'apiKey',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.ApiKey',
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
        )->add(
            'orderCompletedId',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.OrderCompleted',
                'class' => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.title', 'ASC');
                },
                'choice_label' => 'title',
            ]
        )->add(
            'orderCancelledId',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.OrderCancelled',
                'class' => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.title', 'ASC');
                },
                'choice_label' => 'title',
            ]
        )->add(
            'orderExpiredId',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.OrderExpired',
                'class' => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.title', 'ASC');
                },
                'choice_label' => 'title',
            ]
        )->add(
            'orderRefundedId',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.OrderRefunded',
                'class' => OrderStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.title', 'ASC');
                },
                'choice_label' => 'title',
            ]
        );

        $builder->get('orderInitId')->addModelTransformer(new OrderStatusTransformer($options['entityManager']));
        $builder->get('orderCompletedId')->addModelTransformer(new OrderStatusTransformer($options['entityManager']));
        $builder->get('orderCancelledId')->addModelTransformer(new OrderStatusTransformer($options['entityManager']));
        $builder->get('orderExpiredId')->addModelTransformer(new OrderStatusTransformer($options['entityManager']));
        $builder->get('orderRefundedId')->addModelTransformer(new OrderStatusTransformer($options['entityManager']));

        foreach ($options['enabledMethods'] as $method) {
            /*
             * @var FormFactory $formFactory
             */
//            $formFactory = Model::get('form.factory');
//            $subForm = $formFactory->createNamed($method['id']);
//            $subForm->add(
//                'label',
//                TextType::class,
//                [
//                    'required' => false,
//                    'label' => 'lbl.Name',
//                ]
//            );

            $builder->add(
                $builder->create(
                    $method['id'],
                    FormType::class,
                    [
                        'required' => false,
                        'by_reference' => false,
                        'label' => '',
                    ]
                )->add(
                    'enabled',
                    ChoiceType::class,
                    [
                        'required' => false,
                        'label' => 'lbl.Enabled',
                        'placeholder' => false,
                        'choices' => [
                            'lbl.No' => false,
                            'lbl.Yes' => true,
                        ],
                    ]
                )->add(
                    'label',
                    TextType::class,
                    [
                        'required' => false,
                        'label' => 'lbl.Name',
                    ]
                )
            );
        }
//        var_dump($options['enabledMethods']);
//        die();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'enabledMethods' => [],
            ]
        );

        parent::configureOptions($resolver);
    }
}
