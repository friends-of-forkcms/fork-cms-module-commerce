<?php

namespace Backend\Modules\Catalog\Domain\Search;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SearchTypeField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'query',
            SearchTypeField::class,
            [
                'required' => true,
                'label' => 'lbl.SearchTerm',
                'attr' => [
                    'placeholder' => 'lbl.WhatDoYouWantToSearch',
                ]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => SearchDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'search';
    }
}
