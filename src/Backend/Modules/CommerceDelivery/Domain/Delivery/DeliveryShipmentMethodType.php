<?php

namespace Backend\Modules\CommerceDelivery\Domain\Delivery;

use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodType;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tbbc\MoneyBundle\Form\Type\MoneyType;

class DeliveryShipmentMethodType extends ShipmentMethodType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'lbl.Name',
            ])
            ->add('price', MoneyType::class, [
                'required' => true,
                'label' => 'lbl.Price',
            ])
            ->add('vatId', EntityType::class, [
                'required' => true,
                'label' => 'lbl.Vat',
                'class' => Vat::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.sequence', 'ASC');
                },
                'choice_label' => 'title',
            ]);

        $builder
            ->get('vatId')
            ->addModelTransformer(new VatTransformer($options['entityManager']));

        foreach ($options['countries'] as $country) {
            $builder->add(
                $builder->create(
                    $country['id'],
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
                )->add(
                    'price',
                    MoneyType::class,
                    [
                        'required' => true,
                        'label' => 'lbl.Price',
                    ]
                )
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'countries' => [],
            ]
        );

        parent::configureOptions($resolver);
    }
}
