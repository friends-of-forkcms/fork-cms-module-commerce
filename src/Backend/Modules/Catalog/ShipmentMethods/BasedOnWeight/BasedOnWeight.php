<?php

namespace Backend\Modules\Catalog\ShipmentMethods\BasedOnWeight;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\ShipmentMethods\Base\Type;
use Common\Form\CollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class BasedOnWeight extends Type
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add(
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
        )->add(
            'values',
            CollectionType::class,
            [
                'required' => false,
                'entry_type' => ValueType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]
        );
    }
}
