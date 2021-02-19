<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Form\Type\EditorType;
use Backend\Form\Type\MetaType;
use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\ProductDimension\ProductDimensionType;
use Backend\Modules\Catalog\Domain\ProductDimensionNotification\ProductDimensionNotificationType;
use Backend\Modules\Catalog\Domain\ProductSpecial\ProductSpecialType;
use Backend\Modules\Catalog\Domain\SpecificationValue\ProductSpecificationValueDataTransferObject;
use Backend\Modules\Catalog\Domain\SpecificationValue\ProductSpecificationValueType;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\UpSellProduct\UpSellProductType;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroupType;
use Common\Form\CollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.Title',
            ]
        )->add(
            'summary',
            TextareaType::class,
            [
                'required' => true,
                'label' => 'lbl.Summary'
            ]
        )->add(
            'text',
            EditorType::class,
            [
                'required' => false,
                'label' => 'lbl.Content'
            ]
        )->add(
            'weight',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.Weight',
            ]
        )->add(
            'price',
            MoneyType::class,
            [
                'required' => true,
                'label' => 'lbl.Price',
            ]
        )->add(
            'order_quantity',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.OrderQuantity',
            ]
        )->add(
            'stock',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.Stock',
            ]
        )->add(
            'from_stock',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'lbl.FromStock',
                'choices' => [
                    'lbl.Yes' => true,
                    'lbl.No' => false,
                ]
            ]
        )->add(
            'sku',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.ArticleNumber',
            ]
        )->add(
            'ean13',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.EAN13',
            ]
        )->add(
            'isbn',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.ISBN',
            ]
        )->add(
            'sequence',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.Sequence',
            ]
        )->add(
            'type',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'lbl.Type',
                'choices' => [
                    'lbl.Default' => Product::TYPE_DEFAULT,
                    'lbl.ProductWithDimensions' => Product::TYPE_DIMENSIONS,
                ],
            ]
        )->add(
            'hidden',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'lbl.Hidden',
                'choices' => [
                    'lbl.Yes' => true,
                    'lbl.No' => false,
                ],
            ]
        )->add(
            'min_width',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.MinWidth',
            ]
        )->add(
            'min_height',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.MinHeight',
            ]
        )->add(
            'max_width',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.MaxWidth',
            ]
        )->add(
            'max_height',
            NumberType::class,
            [
                'required' => true,
                'label' => 'lbl.MaxHeight',
            ]
        )->add(
            'extra_production_width',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.AddProductionWidth',
            ]
        )->add(
            'extra_production_height',
            NumberType::class,
            [
                'required' => false,
                'label' => 'lbl.AddProductionHeight',
            ]
        )->add(
            'category',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.InCategory',
                'placeholder' => 'lbl.None',
                'class' => Category::class,
                'choices' => $options['categories'],
                'choice_label' => function ($category) {
                    $prefix = null;
                    if ($category->path > 0) {
                        $prefix = str_repeat('-', $category->path) . ' ';
                    }

                    return $prefix . $category->getTitle();
                }
            ]
        )->add(
            'related_products',
            Select2EntityType::class,
            [
                'multiple' => true,
                'remote_route' => 'backend_ajax',
                'remote_params' => [
                    'excluded_id' => ($options['product'] ? $options['product']->getId() : null)
                ],
                'class' => Product::class,
                'primary_key' => 'id',
                'text_property' => 'getTitle',
                'minimum_input_length' => 3,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => false,
                'cache_timeout' => 60000, // if 'cache' is true
                'language' => Locale::workingLocale(),
                'label' => 'lbl.RelatedProducts',
                'action' => 'AutoCompleteProducts',
            ]
        )->add(
                'up_sell_products',
                CollectionType::class,
                [
                    'required' => false,
                    'label' => 'lbl.UpSellProducts',
                    'entry_type' => UpSellProductType::class,
                    'entry_options' => [
                        'product' => ($options['product'] ? $options['product'] : null),
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ]
        )->add(
            $builder->create(
                'specification_values',
                CollectionType::class,
                [
                    'required' => false,
                    'entry_type' => ProductSpecificationValueType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label' => 'lbl.Specifications',
                ]
            )->addModelTransformer(new CallbackTransformer(
                function ($entities) {
                    $dataTransferObjects = [];

                    foreach ($entities as $entity) {
                        $dataTransferObjects[] = new ProductSpecificationValueDataTransferObject($entity);
                    }

                    return $dataTransferObjects;
                },
                function ($dataTransferObjects) {
                    $entities = [];
                    $entityManager = Model::get('doctrine.orm.entity_manager');

                    /**
                     * @var ProductSpecificationValueDataTransferObject[] $dataTransferObjects
                     */
                    foreach ($dataTransferObjects as $dataTransferObject) {
                        $specificationValue = $dataTransferObject->value;

                        // Determine if the value not exists
                        if (!$entityManager->contains($dataTransferObject->value)) {
                            $specificationValue->setSpecification($dataTransferObject->specification);
                            $specificationValue->setMeta($dataTransferObject->getMeta());
                        }

                        $entities[] = $specificationValue;
                    }

                    return $entities;
                }
            ))
        )->add(
            'specials',
            CollectionType::class,
            [
                'required' => false,
                'entry_type' => ProductSpecialType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'lbl.Offer',
            ]
        )->add(
            'dimensions',
            CollectionType::class,
            [
                'required' => false,
                'entry_type' => ProductDimensionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => '',
            ]
        )->add(
            'dimension_notifications',
            CollectionType::class,
            [
                'required' => false,
                'entry_type' => ProductDimensionNotificationType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'lbl.Notifications',
            ]
        )->add(
            'dimension_instructions',
            EditorType::class,
            [
                'required' => false,
                'label' => 'lbl.DimensionInstructions'
            ]
        )->add(
            'brand',
            EntityType::class,
            [
                'label' => 'lbl.Brand',
                'placeholder' => 'lbl.None',
                'class' => Brand::class,
                'choice_label' => 'title',
                'required' => true,
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
                'choice_label' => 'title'
            ]
        )->add(
            'stock_status',
            EntityType::class,
            [
                'label' => 'lbl.NotInStockStatus',
                'class' => StockStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.title', 'ASC');
                },
                'choice_label' => 'title'
            ]
        )->add(
            'images',
            MediaGroupType::class,
            [
                'required' => false,
                'label' => 'lbl.Images',
            ]
        )->add(
            'downloads',
            MediaGroupType::class,
            [
                'required' => false,
                'label' => 'lbl.Downloads',
            ]
        )->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $event->getForm()->add(
                    'meta',
                    MetaType::class,
                    [
                        'base_field_name' => 'title',
                        'generate_url_callback_class' => 'catalog.repository.product',
                        'generate_url_callback_method' => 'getUrl',
                        'detail_url' => '',
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
                'data_class' => ProductDataTransferObject::class,
                'categories' => null,
                'product' => null,
                'validation_groups' => ['Default'],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product';
    }
}
