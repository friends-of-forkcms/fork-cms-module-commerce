<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue;

use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ProductSpecificationValueType extends AbstractType
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
            Select2EntityType::class,
            [
                'multiple'             => false,
                'remote_route'         => 'backend_ajax',
                'class'                => SpecificationValue::class,
                'primary_key'          => 'id',
                'text_property'        => 'value',
                'minimum_input_length' => 1,
                'page_limit'           => 10,
                'allow_clear'          => false,
                'allow_add'            => [
                    'enabled'        => true,
                    'new_tag_text'   => ' (' . Language::lbl('New') .')',
                    'tag_separators' => '[","]',
                ],
                'delay'                => 250,
                'cache'                => false,
                'cache_timeout'        => 60000, // if 'cache' is true
                'language'             => Locale::workingLocale(),
                'label'                => 'lbl.Value',
                'action'               => 'AutoCompleteSpecificationValue',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductSpecificationValueDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'specification_values';
    }
}
