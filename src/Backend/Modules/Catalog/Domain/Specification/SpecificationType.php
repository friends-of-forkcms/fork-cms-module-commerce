<?php

namespace Backend\Modules\Catalog\Domain\Specification;

use Backend\Form\Type\MetaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.Title',
            ]
        )->add(
            'type',
            ChoiceType::class,
            [
                'required' => true,
                'label'    => 'lbl.Type',
                'choices'  => array('lbl.Textbox' => Specification::textbox)
            ]
        )->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $event->getForm()->add(
                    'meta',
                    MetaType::class,
                    [
                        'base_field_name'                  => 'title',
                        'generate_url_callback_class'      => 'catalog.repository.specification',
                        'generate_url_callback_method'     => 'getUrl',
                        'detail_url'                       => '',
                        'generate_url_callback_parameters' => [
                            $event->getData()->locale,
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
                'data_class' => SpecificationDataTransferObject::class
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'specification';
    }
}
