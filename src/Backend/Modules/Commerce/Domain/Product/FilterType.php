<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Category\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'required' => false,
                'label' => 'lbl.FilterProducts',
                'attr' => ['placeholder' => 'lbl.FilterProducts']
            ])
            ->add('brand', EntityType::class, [
                'required' => false,
                'label' => 'lbl.FilterByBrand',
                'placeholder' => 'lbl.FilterByBrand',
                'class' => Brand::class,
                'choices' => $options['brands'],
                'choice_label' => fn (Brand $brand) => $brand->getTitle(),
            ])
            ->add('category', EntityType::class, [
                'required' => false,
                'label' => 'lbl.FilterByCategory',
                'placeholder' => 'lbl.FilterByCategory',
                'class' => Category::class,
                'choices' => $options['categories'],
                'choice_label' => function (Category $category) {
                    $prefix = null;
                    if ($category->path > 0) {
                        $prefix = str_repeat('-', $category->path) . ' ';
                    }

                    return $prefix . $category->getTitle();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'categories' => null,
                'brands' => null,
            ]
        );
    }
}
