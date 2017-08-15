<?php

namespace Backend\Modules\Catalog\Domain\Vat;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'vat',
            EntityType::class,
            [
                'required'     => false,
                'label'        => 'lbl.Vat',
                'placeholder'  => 'lbl.None',
                'class'        => Vat::class,
                'choices'      => $options['vats'],
                'choice_label' => function($vat) {
                    $prefix = null;
                    if ($vat->path > 0) {
                        $prefix = str_repeat('-', $vat->path) .' ';
                    }

                    return $prefix . $vat->getTitle();
                }
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'vats' => null
            ]
        );
    }
}
