<?php

namespace Backend\Modules\Catalog\ShipmentMethods\Pickup;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\ShipmentMethods\Base\Type;
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
                'label'    => 'lbl.Name',
            ]
        )->add(
            'price',
            MoneyType::class,
            [
                'required' => true,
                'label'    => 'lbl.Price',
            ]
        )->add(
            'vat',
            EntityType::class,
            [
                'required' => true,
                'label'    => 'lbl.Vat',
                'class'    => Vat::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                              ->orderBy('i.sequence', 'ASC');
                },
                'choice_label'  => 'title'
            ]
        );
    }
}
