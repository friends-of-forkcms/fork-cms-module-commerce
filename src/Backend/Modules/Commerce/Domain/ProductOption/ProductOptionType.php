<?php

namespace Backend\Modules\Commerce\Domain\ProductOption;

use Backend\Form\Type\EditorType;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotificationType;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Common\Form\CollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tbbc\MoneyBundle\Form\Type\MoneyType;

class ProductOptionType extends AbstractType
{
    public static array $typeChoices = [
        'lbl.DropDown' => ProductOption::DISPLAY_TYPE_DROP_DOWN,
        'lbl.RadioButton' => ProductOption::DISPLAY_TYPE_RADIO_BUTTON,
        'lbl.Color' => ProductOption::DISPLAY_TYPE_COLOR,
        'lbl.SquareUnit' => ProductOption::DISPLAY_TYPE_SQUARE_UNIT,
        'lbl.Piece' => ProductOption::DISPLAY_TYPE_PIECE,
        'lbl.ValueBetweenValues' => ProductOption::DISPLAY_TYPE_BETWEEN,
        'lbl.Text' => ProductOption::DISPLAY_TYPE_TEXT,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'lbl.Title',
            ])
            ->add('required', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.Required',
            ])
            ->add('custom_value_allowed', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.CustomValueAllowed',
            ])
            ->add('custom_value_price', MoneyType::class, [
                'required' => false,
                'label' => 'lbl.CustomValuePrice',
            ])
            ->add('placeholder', TextType::class, [
                'required' => false,
                'label' => 'lbl.Placeholder',
            ])
            ->add('prefix', TextType::class, [
                'required' => false,
                'label' => 'lbl.Prefix',
            ])
            ->add('suffix', TextType::class, [
                'required' => false,
                'label' => 'lbl.Suffix',
            ])
            ->add('text', EditorType::class, [
                'required' => false,
                'label' => 'lbl.Description',
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'label' => 'lbl.Type',
                'placeholder' => 'lbl.MakeAChoice',
                'choices' => self::$typeChoices,
            ])
            ->add('dimension_notifications', CollectionType::class, [
                'required' => false,
                'entry_type' => ProductDimensionNotificationType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'lbl.Notifications',
            ])
            ->add('parent_product_option_value', EntityType::class, [
                'required' => false,
                'label' => 'lbl.ParentProductOptionValue',
                'placeholder' => 'lbl.None',
                'class' => ProductOptionValue::class,
                'query_builder' => function (EntityRepository $entityRepository) use ($options) {
                    if (!$options['data']->hasExistingProductOption() || !$options['product']) {
                        return;
                    }

                    $queryBuilder = $entityRepository->createQueryBuilder('i');
                    if ($options['data']->hasExistingProductOption()) {
                        $queryBuilder->andWhere('i.product_option != :product_option')
                            ->setParameter('product_option', $options['data']->getProductOptionEntity());
                    }

                    if ($options['product']) {
                        $queryBuilder->innerJoin('i.product_option', 'op')
                            ->andWhere('op.product = :product')
                            ->setParameter('product', $options['product']);
                    }

                    return $queryBuilder;
                },
                'choice_label' => function (ProductOptionValue $productOptionValue) {
                    return $productOptionValue->getProductOption()->getTitle() . ' - ' . $productOptionValue->getTitle();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductOptionDataTransferObject::class,
                'product' => null,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product_option';
    }
}
