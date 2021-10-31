<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Pickup;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Backend\Modules\Commerce\ShipmentMethods\Base\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PickupType extends Type
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
            $builder
                ->create(
                    'price',
                    MoneyType::class,
                    [
                        'required' => true,
                        'label' => 'lbl.Price',
                    ]
                )
                ->addModelTransformer(new MoneyToLocalizedStringTransformer())
        )->add(
            'vat',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.Vat',
                'class' => Vat::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                              ->orderBy('i.sequence', 'ASC');
                },
                'choice_label' => 'title',
            ]
        );
    }
}
