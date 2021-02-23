<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue;

use Backend\Form\Type\MetaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecificationValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'value',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.Title',
            ]
        )->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $event->getForm()->add(
                    'meta',
                    MetaType::class,
                    [
                        'base_field_name'                  => 'value',
                        'generate_url_callback_class'      => 'commerce.repository.specification_value',
                        'generate_url_callback_method'     => 'getUrl',
                        'detail_url'                       => '',
                        'generate_url_callback_parameters' => [
                            $event->getData()->specification->getId(),
                            $event->getData()->id,
                        ],
                    ]
                );
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => SpecificationValueDataTransferObject::class
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'specification_value';
    }
}
