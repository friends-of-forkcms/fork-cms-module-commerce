<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValueRepository;
use Backend\Modules\Commerce\Domain\UpSellProduct\UpSellProduct;
use Backend\Modules\Commerce\Form\BetweenType;
use Backend\Modules\Commerce\Form\ChoiceTypeExtension;
use Backend\Modules\Commerce\Form\ColorType;
use Backend\Modules\Commerce\Form\EntityTypeExtension;
use Backend\Modules\Commerce\Form\TextTypeExtension;
use Common\Core\Model;
use Frontend\Core\Language\Language;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class AddToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var Product $product
         */
        $product = $options['product'];

        $builder->add(
            'id',
            HiddenType::class,
            [
                'required' => true,
            ]
        )->add(
            'quote',
            HiddenType::class,
            [
                'required' => true,
                'data' => 1,
            ]
        )->add(
            'overwrite',
            HiddenType::class,
            [
                'required' => true,
                'data' => 1,
            ]
        );

        if ($product->inStock()) {
            $builder->add(
                'amount',
                IntegerType::class,
                [
                    'required' => true,
                    'label' => 'lbl.Amount',
                    'scale' => 0,
                    'attr' => [
                        'step' => 1,
                        'min' => 1,
                    ],
                ]
            );
        } else {
            $builder->add(
                'amount',
                HiddenType::class,
                [
                    'required' => true,
                ]
            );
        }

        if ($product->usesDimensions()) {
            $widthConstraints = [];
            $heightConstraints = [];

            if ($product->getMinWidth() && $product->getMaxWidth()) {
                $widthConstraints[] = new Range([
                    'min' => $product->getMinWidth(),
                    'max' => $product->getMaxWidth(),
                    'minMessage' => str_replace('%s', '{{ limit }}', Language::err('TheMinimalWidthIs')),
                    'maxMessage' => str_replace('%s', '{{ limit }}', Language::err('TheMaximalWidthIs')),
                ]);
            }

            if ($product->getMinHeight() && $product->getMaxHeight()) {
                $heightConstraints[] = new Range([
                    'min' => $product->getMinHeight(),
                    'max' => $product->getMaxHeight(),
                    'minMessage' => str_replace('%s', '{{ limit }}', Language::err('TheMinimalHeightIs')),
                    'maxMessage' => str_replace('%s', '{{ limit }}', Language::err('TheMaximalHeightIs')),
                ]);
            }

            $builder->add(
                'width',
                NumberType::class,
                [
                    'required' => false,
                    'label' => 'lbl.Width',
                    'scale' => 0,
                    'attr' => [
                        'autocomplete' => 'off',
                        'data-lpignore' => 'true',
                        'data-min-value' => $product->getMinWidth(),
                        'data-max-value' => $product->getMaxWidth(),
                        'data-min-error' => 'TheMinimalWidthIs',
                        'data-max-error' => 'TheMaximalWidthIs',
                    ],
                    'constraints' => $widthConstraints,
                ]
            )->add(
                'height',
                NumberType::class,
                [
                    'required' => false,
                    'label' => 'lbl.Height',
                    'scale' => 0,
                    'attr' => [
                        'autocomplete' => 'off',
                        'data-lpignore' => 'true',
                        'data-min-value' => $product->getMinHeight(),
                        'data-max-value' => $product->getMaxHeight(),
                        'data-min-error' => 'TheMinimalHeightIs',
                        'data-max-error' => 'TheMaximalHeightIs',
                    ],
                    'constraints' => $heightConstraints,
                ]
            );
        }

        $this->addProductOptions($product->getProductOptions(), $builder);

        if ($product->getUpSellProducts()->count() > 0) {
            $builder->add(
                'up_sell',
                EntityType::class,
                [
                    'required' => false,
                    'label' => 'Optionele accessoires',
                    'class' => UpSellProduct::class,
                    'choices' => $product->getUpSellProducts(),
                    'choice_label' => function (UpSellProduct $upSellProduct) {
                        return $upSellProduct->getUpSellProduct()->getTitle();
                    },
                    'choice_value' => function (UpSellProduct $upSellProduct) {
                        return $upSellProduct->getUpSellProduct()->getId();
                    },
                    'expanded' => true,
                    'multiple' => true,
                ]
            );
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($product) {
            $data = $this->preSubmitEvent($event->getData(), $product->getProductOptions());

            $event->setData($data);
        });
    }

    /**
     * @param ProductOption[] $productOptions
     *
     * @return mixed
     */
    private function preSubmitEvent(array $data, $productOptions)
    {
        foreach ($productOptions as $productOption) {
            if ($productOption->isTextType() || $productOption->isColorType()) {
                continue;
            }

            foreach ($productOption->getProductOptionValues() as $productOptionValue) {
                $data = $this->preSubmitEvent($data, $productOptionValue->getProductOptions());
            }

            if (!$productOption->isCustomValueAllowed()) {
                continue;
            }

            $name = 'option_' . $productOption->getId();
            $customValueName = $name . '_custom_value';

            if ($data[$name] == 'custom_value' && array_key_exists($customValueName, $data)) {
                $data[$name] = null;
            } else {
                $data[$customValueName] = null;
            }
        }

        return $data;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'product' => null,
            'csrf_protection' => false,
            'validation_groups' => function (FormInterface $form) {
                /**
                 * @var AddToCartDataTransferObject $data
                 */
                $data = $form->getData();

                $groups = ['Default'];

                if ($data->product->usesDimensions()) {
                    $groups[] = 'Dimensions';
                }

                return $groups;
            },
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'product';
    }

    /**
     * @param ProductOption[] $productOptions
     * @param string          $parent
     * @param string          $parentValue
     */
    private function addProductOptions($productOptions, FormBuilderInterface $builder, string $parent = null, int $parentValue = null, bool $hidden = false): void
    {
        foreach ($productOptions as $productOption) {
            $name = 'option_' . $productOption->getId();
            $type = null;
            $modelTransformer = null;
            $params = [
                'required' => $productOption->isRequired(),
                'label' => $productOption->getTitle(),
                'help' => $productOption->getText(),
                'allow_custom_value' => $productOption->isCustomValueAllowed(),
                'attr' => [
                    'data-parent' => $parent,
                    'data-parent-value' => $parentValue,
                    'data-hidden' => $hidden,
                    'data-option' => $productOption->getId(),
                ],
            ];

            $this->addDependencies($params, $productOption);

            switch ($productOption->getType()) {
                case ProductOption::DISPLAY_TYPE_SQUARE_UNIT:
                case ProductOption::DISPLAY_TYPE_DROP_DOWN:
                    $type = EntityTypeExtension::class;

                    $params['placeholder'] = $this->getPlaceholder($productOption);
                    $params['class'] = ProductOptionValue::class;
                    $params['choice_label'] = function (ProductOptionValue $value) {
                        return $value->getTitle();

                        $label = $value->getTitle();
                        if ($value->getPrice()) {
                            $label .= ' (€ ' . number_format($value->getPrice(), 2, ',', '.') . ')';
                        }

                        return $label;
                    };
                    $params['choices'] = $productOption->getProductOptionValues();

                    break;
                case ProductOption::DISPLAY_TYPE_RADIO_BUTTON:
                    $type = ChoiceTypeExtension::class;

                    $params['expanded'] = true;
                    $params['placeholder'] = false;
                    $params['choice_label'] = function (ProductOptionValue $value) {
                        return $value->getTitle();

                        $label = $value->getTitle();
                        if ($value->getPrice()) {
                            $label .= ' (€ ' . number_format($value->getPrice(), 2, ',', '.') . ')';
                        }

                        return $label;
                    };
                    $params['choices'] = $productOption->getProductOptionValues();

                    break;
                case ProductOption::DISPLAY_TYPE_COLOR:
                    $type = ColorType::class;
                    $params['attr']['placeholder'] = $productOption->getPlaceholder();
                    $params['choice_label'] = function (ProductOptionValue $value) {
                        $label = $value->getTitle();

                        return $label;
                    };
                    $params['choices'] = $productOption->getProductOptionValues();

                    break;
                case ProductOption::DISPLAY_TYPE_BETWEEN:
                    $type = BetweenType::class;
                    $values = [];

                    // Add the root element
                    $builder->add(
                        $builder->create(
                            $name,
                            HiddenType::class
                        )->addModelTransformer(
                            new CallbackTransformer(
                                function (?ProductOptionValue $input) {
                                    $value = null;

                                    if ($input) {
                                        $value = $input->getId();
                                    }

                                    return $value;
                                },
                                function ($reverseTransform) use ($productOption) {
                                    /**
                                     * @var ProductOptionValueRepository $productOptionValueRepository
                                     */
                                    $productOptionValueRepository = Model::get('commerce.repository.product_option_value');

                                    return $productOptionValueRepository->findOneById($reverseTransform, $productOption);
                                }
                            )
                        )
                    );

                    foreach ($productOption->getProductOptionValues() as $productOptionValue) {
                        $values[] = [
                            'id' => $productOptionValue->getId(),
                            'start' => $productOptionValue->getStart(),
                            'end' => $productOptionValue->getEnd(),
                        ];
                    }

                    $modelTransformer = new CallbackTransformer(function ($input) {
                        return $input;
                    }, function ($reverseTransform) {
                        return $reverseTransform;
                    });

//                    $params['class'] = ProductOptionValue::class;
//                    $params['choices'] = $productOption->getProductOptionValues();
//                    $params['choice_label'] = function(ProductOptionValue $productOptionValue) {
//                        $label = [];
//
//                        if ($productOptionValue->getStart()) {
//                            $label[] = $productOptionValue->getProductOption()->getPrefix() .
//                                $productOptionValue->getStart().
//                                $productOptionValue->getProductOption()->getSuffix();
//                        }
//
//                        if ($productOptionValue->getEnd()) {
//                            $label[] = $productOptionValue->getProductOption()->getPrefix() .
//                                $productOptionValue->getEnd().
//                                $productOptionValue->getProductOption()->getSuffix();
//                        }
//
//                        return implode(' - ', $label);
//                    };
                    $params['attr']['placeholder'] = $productOption->getPlaceholder();
                    $params['attr']['prefix'] = $productOption->getPrefix();
                    $params['attr']['suffix'] = $productOption->getSuffix();
                    $params['attr']['data-between-values'] = json_encode($values, JSON_THROW_ON_ERROR);
                    $params['attr']['data-related-field'] = $name;
                    $name .= '_custom_value';

                    break;
                case ProductOption::DISPLAY_TYPE_TEXT:
                    $type = TextTypeExtension::class;

                    break;
            }

            // Skip unknown type
            if (!$type) {
                continue;
            }

            $field = $builder->create($name, $type, $params);
            if ($modelTransformer) {
                $field->addModelTransformer($modelTransformer);
            }
            $builder->add($field);

            if ($productOption->isCustomValueAllowed()) {
                $fieldName = 'option_' . $productOption->getId() . '_custom_value';
                if ($productOption->isColorType()) {
                    $builder->add(
                        $fieldName,
                        TextType::class,
                        [
                            'required' => false,
                            'label' => 'lbl.OtherRALColor',
                            'attr' => [
                                'data-custom-value' => $builder->getName() . '_option_' . $productOption->getId(),
                                'data-option-type' => $productOption->getType(),
                                'placeholder' => 'lbl.EnterYourOwnRALColor',
                            ],
                        ]
                    );
                } else {
                    $builder->add(
                        $fieldName,
                        NumberType::class,
                        [
                            'required' => false,
                            'label' => 'lbl.OtherValue',
                            'scale' => 0,
                            'attr' => [
                                'data-custom-value' => $builder->getName() . '_option_' . $productOption->getId(),
                                'data-option-type' => $productOption->getType(),
                                'prefix' => $productOption->getPrefix(),
                                'suffix' => $productOption->getSuffix(),
                            ],
                        ]
                    );
                }
            }

            foreach ($productOption->getProductOptionValues() as $key => $productOptionValue) {
                if ($productOption->getType() == ProductOption::DISPLAY_TYPE_RADIO_BUTTON) {
                    $nextParentValue = $key;
                } else {
                    $nextParentValue = $productOptionValue->getId();
                }

                $this->addProductOptions(
                    $productOptionValue->getProductOptions(),
                    $builder,
                    $name,
                    $nextParentValue,
                    true
                );
            }
        }
    }

    /**
     * Add the dependencies to the params value when they exists.
     */
    private function addDependencies(array &$params, ProductOption $productOption): void
    {
        $dependencies = [];
        $i = 0;

        foreach ($productOption->getProductOptionValues() as $productOptionValue) {
            if ($productOptionValue->getDependencies()->isEmpty()) {
                continue;
            }

            if ($productOption->getType() == ProductOption::DISPLAY_TYPE_RADIO_BUTTON) {
                $key = $i++;
            } else {
                $key = $productOption->getId();
            }

            if (!array_key_exists($productOptionValue->getId(), $dependencies)) {
                $dependencies[$key] = [];
            }

            $j = 0;
            foreach ($productOptionValue->getDependencies() as $dependency) {
                $productOptionId = $dependency->getProductOption()->getId();
                if (!array_key_exists($productOptionId, $dependencies[$key])) {
                    $dependencies[$key][$productOptionId] = [];
                }

                if ($dependency->getProductOption()->getType() == ProductOption::DISPLAY_TYPE_RADIO_BUTTON) {
                    $dependencyKey = $j++;
                } else {
                    $dependencyKey = $dependency->getId();
                }

                $dependencies[$key][$productOptionId][] = $dependencyKey;
            }
        }

        // Only set when exists
        if (!empty($dependencies)) {
            $params['attr']['data-dependencies'] = json_encode($dependencies, JSON_THROW_ON_ERROR);
        }
    }

    /**
     * Get the placeholder based on the product option.
     */
    private function getPlaceholder(ProductOption $productOption): string
    {
        $placeholder = $productOption->getPlaceholder();

        if (!$placeholder) {
            $placeholder = 'lbl.MakeAChoicePlaceholder';
        }

        return $placeholder;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /**
         * @var Product $product
         */
        $product = $options['product'];

        $this->addExtraViewOptions($product->getProductOptions(), $view, $form, $options);
    }

    /**
     * @param ProductOption[] $productOptions
     */
    private function addExtraViewOptions($productOptions, FormView $view, FormInterface $form, array $options)
    {
        foreach ($productOptions as $productOption) {
            $name = 'option_' . $productOption->getId();

            if ($productOption->isCustomValueAllowed() && !$productOption->isColorType()) {
                switch ($productOption->getType()) {
                    case ProductOption::DISPLAY_TYPE_SQUARE_UNIT:
                    case ProductOption::DISPLAY_TYPE_DROP_DOWN:
                        $newChoice = new ChoiceView([], 'custom_value', Language::lbl('EnterCustomValue'));
                        $view->children[$name]->vars['choices'][] = $newChoice;

                        break;
                }
            }

            foreach ($productOption->getProductOptionValues() as $productOptionValue) {
                $this->addExtraViewOptions(
                    $productOptionValue->getProductOptions(),
                    $view,
                    $form,
                    $options
                );
            }
        }
    }
}
