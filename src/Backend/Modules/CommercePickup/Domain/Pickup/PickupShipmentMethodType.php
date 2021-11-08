<?php

namespace Backend\Modules\CommercePickup\Domain\Pickup;

use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodType;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatTransformer;
use Backend\Modules\Commerce\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PickupShipmentMethodType extends ShipmentMethodType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', TextType::class, [
                    'required' => true,
                    'label' => 'lbl.Name',
            ])
            ->add(
                $builder
                    ->create('price', MoneyType::class, [
                        'required' => true,
                        'label' => 'lbl.Price',
                    ])
                    ->addModelTransformer(new MoneyToLocalizedStringTransformer())
            )
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
    }
}
