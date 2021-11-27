<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search_query', TextType::class, [
                'required' => false,
                'label' => 'lbl.Search',
            ])
            ->add('order_status', EntityType::class, [
                'required' => false,
                'label' => 'lbl.OrderStatus',
                'placeholder' => 'lbl.None',
                'class' => OrderStatus::class,
                'choices' => $options['order_statuses'],
                'choice_label' => fn (OrderStatus $orderStatus) => $orderStatus->getTitle(),
            ])
            ->add(
                $builder
                    ->create('order_date_range', TextType::class, [
                        'required' => false,
                        'label' => 'lbl.OrderDate',
                    ])
                    ->addModelTransformer(new CallbackTransformer(
                        function (array $daterangeAsArray = null) {
                            $daterangeAsArray = array_filter($daterangeAsArray);

                            return !empty($daterangeAsArray) ? implode(' - ', $daterangeAsArray) : null;
                        },
                        function (string $daterangeAsString) {
                            [$startedAt, $endedAt] = explode(' - ', $daterangeAsString);
                            $dateTimeStartedAt = DateTime::createFromFormat('d-m-Y', $startedAt);
                            $dateTimeEndedAt = DateTime::createFromFormat('d-m-Y', $endedAt);

                            return [$dateTimeStartedAt, $dateTimeEndedAt];
                        }
                    ))
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'order_statuses' => null,
            ]
        );
    }
}
