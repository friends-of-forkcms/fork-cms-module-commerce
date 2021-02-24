<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroupType;
use Common\Form\CollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var ProductOption $productOption
         */
        $productOption = $options['product_option'];

        $builder->add(
            'sub_title',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.SubTitle',
            ]
        )->add(
            'sku',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.ArticleNumber',
            ]
        )->add(
            'price',
            MoneyType::class,
            [
                'required' => false,
                'label' => 'lbl.Price',
            ]
        )->add(
            'percentage',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.Percentage',
                'scale' => 1,
            ]
        )->add(
            'impact_type',
            ChoiceType::class,
            [
                'required' => false,
                'label' => 'lbl.ImpactType',
                'placeholder' => false,
                'choices' => [
                    'lbl.Add' => ProductOptionValue::IMPACT_TYPE_ADD,
                    'lbl.Subtract' => ProductOptionValue::IMPACT_TYPE_SUBTRACT,
                ],
            ]
        )->add(
            'vat',
            EntityType::class,
            [
                'label' => 'lbl.Vat',
                'class' => Vat::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.sequence', 'ASC');
                },
                'choice_label' => 'title',
            ]
        )->add(
            'default_value',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'lbl.DefaultValue',
            ]
        )->add(
            $builder->create(
                'dependencies',
                CollectionType::class,
                [
                    'required' => false,
                    'entry_type' => ProductOptionValueDependenciesType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label' => 'lbl.Specifications',
                    'entry_options' => [
                        'product_option' => $productOption,
                    ],
                ]
            )
                ->addModelTransformer(new CallbackTransformer(
                    function ($entities) {
                        return $entities;
                    },
                    function ($dataTransferObjects) {
                        $entities = [];

                        /**
                         * @var ProductOptionValueDependencyDataTransferObject $dataTransferObject
                         */
                        foreach ($dataTransferObjects as $dataTransferObject) {
                            $entities = [...$entities, ...$dataTransferObject->values];
                        }

                        return $entities;
                    }
                ))
        );

        if ($productOption->isBetweenType()) {
            $builder->add(
                'start',
                NumberType::class,
                [
                    'required' => true,
                    'label' => 'lbl.Start',
                    'scale' => 0,
                ]
            )->add(
                'end',
                NumberType::class,
                [
                    'required' => true,
                    'label' => 'lbl.End',
                    'scale' => 0,
                ]
            );
        } else {
            $builder->add(
                'title',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'lbl.Title',
                ]
            );
        }

        if ($productOption->isColorType()) {
            $builder->add(
                'hex_value',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'lbl.HTMLHexValue',
                ]
            )->add(
                'image',
                MediaGroupType::class,
                [
                    'required' => false,
                    'label' => 'lbl.Image',
                    'maximum_items' => 1,
                ]
            );
        }

        if ($productOption->getProduct()->usesDimensions()) {
            $builder->add(
                'width',
                NumberType::class,
                [
                    'required' => false,
                    'label' => 'lbl.Width',
                    'scale' => 0,
                ]
            )->add(
                'height',
                NumberType::class,
                [
                    'required' => false,
                    'label' => 'lbl.Height',
                    'scale' => 0,
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductOptionValueDataTransferObject::class,
                'product_option' => '',
                'validation_groups' => function (FormInterface $form) {
                    /**
                     * @var ProductOptionValueDataTransferObject $data
                     */
                    $data = $form->getData();
                    $validationGroups = ['Default'];

                    if ($data->productOption->isBetweenType()) {
                        $validationGroups[] = 'BetweenType';
                    } else {
                        $validationGroups[] = 'DefaultTypes';
                    }

                    return $validationGroups;
                },
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product_option_value';
    }
}
