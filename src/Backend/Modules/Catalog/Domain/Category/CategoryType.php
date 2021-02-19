<?php

namespace Backend\Modules\Catalog\Domain\Category;

use Backend\Core\Language\Locale;
use Backend\Form\Type\EditorType;
use Backend\Form\Type\MetaType;
use Backend\Modules\Catalog\Domain\Product\Product;
use Common\Form\ImageType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class CategoryType extends AbstractType
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
            'text',
            EditorType::class,
            [
                'required' => false,
                'label' => 'lbl.Description',
            ]
        )->add(
            'intro',
            EditorType::class,
            [
                'required' => false,
                'label' => 'lbl.Summary',
            ]
        )->add(
            'image',
            ImageType::class,
            [
                'required' => false,
                'label' => 'lbl.Image',
                'image_class' => Image::class,
            ]
        )->add(
            'googleTaxonomyId',
            ChoiceType::class,
            [
                'required' => false,
                'label' => 'lbl.GoogleTaxonomyId',
                'choices' => $options['google_taxonomies'],
                'attr' => [
                    'class' => 'select2simple',
                ]
            ]
        )->add(
            'parent',
            EntityType::class,
            [
                'required' => false,
                'label' => 'lbl.InCategory',
                'placeholder' => 'lbl.None',
                'class' => Category::class,
                'choices' => $options['categories'],
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $queryBuilder = $er->createQueryBuilder('i');
                    if ($options['current_category']) {
                        $queryBuilder = $queryBuilder->where('i.id != :category')
                            ->setParameter('category', $options['current_category']);
                    }

                    return $queryBuilder;
                },
                'choice_label' => function ($category) {
                    $prefix = null;
                    if ($category->path > 0) {
                        $prefix = str_repeat('-', $category->path) . ' ';
                    }

                    return $prefix . $category->getTitle();
                }
            ]
        )->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $event->getForm()->add(
                    'meta',
                    MetaType::class,
                    [
                        'base_field_name' => 'title',
                        'generate_url_callback_class' => 'catalog.repository.category',
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
        $resolver->setDefaults([
            'data_class' => CategoryDataTransferObject::class,
            'categories' => null,
            'google_taxonomies' => [],
            'current_category' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'category';
    }
}
