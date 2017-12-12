<?php

namespace Backend\Modules\Catalog\Domain\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'overview_num_items',
            NumberType::class,
            [
                'required' => true,
                'label'    => 'lbl.ItemsPerPage',
            ]
        )->add(
            'filters_show_more_num_items',
            NumberType::class,
            [
                'required' => true,
                'label'    => 'lbl.ShowMoreAfterFilterItems',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => SettingsDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'settings';
    }
}
