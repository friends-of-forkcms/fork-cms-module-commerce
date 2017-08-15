<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue;

use Backend\Modules\Catalog\Domain\Specification\Specification;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecificationValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'specification',
            EntityType::class,
            [
                'required'     => true,
                'label'        => 'lbl.Specification',
                'class'        => Specification::class,
                'choice_label' => 'title'
            ]
        )->add(
            'value',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.Value',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => SpecificationValue::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'specification_values';
    }
}
