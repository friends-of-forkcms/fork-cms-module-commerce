<?php

namespace Backend\Modules\Catalog\Domain\Specification;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'specification',
            EntityType::class,
            [
                'required'     => false,
                'label'        => 'lbl.Specification',
                'placeholder'  => 'lbl.None',
                'class'        => Specification::class,
                'choices'      => $options['specifications'],
                'choice_label' => function($specification) {
                    $prefix = null;
                    if ($specification->path > 0) {
                        $prefix = str_repeat('-', $specification->path) .' ';
                    }

                    return $prefix . $specification->getTitle();
                }
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'specifications' => null
            ]
        );
    }
}
