<?php

namespace Backend\Modules\Catalog\Domain\Country;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.Name',
            ]
        )->add(
            'iso',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.IsoCode'
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => CountryDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'country';
    }
}
