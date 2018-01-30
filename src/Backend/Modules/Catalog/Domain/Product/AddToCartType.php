<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                NumberType::class,
                [
                    'required' => true,
                    'label' => 'lbl.Amount',
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

        foreach ($product->getProductOptions() as $productOption) {
            switch ($productOption->getType()) {
                case ProductOption::DISPLAY_TYPE_DROP_DOWN:
                    $builder->add(
                        'option_' . $productOption->getId(),
                        EntityType::class,
                        [
                            'required' => $productOption->isRequired(),
                            'label' => $productOption->getTitle(),
                            'placeholder' => $this->getPlaceholder($productOption),
                            'class' => ProductOptionValue::class,
                            'choice_label' => function (ProductOptionValue $value) {
                                $label = $value->getTitle();

                                if ($value->getPrice()) {
                                    $label .= ' (€ ' . number_format($value->getPrice(), 2, ',', '.') . ')';
                                }

                                return $label;
                            },
                            'choices' => $productOption->getProductOptionValues(),
                        ]
                    );
            }
        }

        $builder->add(
            'up_sell',
            EntityType::class,
            [
                'required' => false,
                'label' => 'Optionele accessoires',
                'class' => Product::class,
                'choices' => $product->getUpSellProducts(),
                'choice_label' => 'title',
                'expanded' => true,
                'multiple' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'product' => null,
                'csrf_protection' => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product';
    }

    /**
     * Get the placeholder based on the product option
     *
     * @param ProductOption $productOption
     *
     * @return string
     */
    private function getPlaceholder(ProductOption $productOption): string
    {
        $placeholder = $productOption->getPlaceholder();

        if (!$placeholder) {
            $placeholder = '- maak een keuze -';
        }

        return $placeholder;
    }
}
