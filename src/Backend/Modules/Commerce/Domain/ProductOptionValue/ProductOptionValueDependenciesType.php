<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ProductOptionValueDependenciesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var ProductOption $productOption
         */
        $productOption = $options['product_option'];

        $builder->add(
            'product_option',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.ProductOption',
                'class' => ProductOption::class,
                'query_builder' => function (EntityRepository $er) use ($productOption) {
                    return $er->createQueryBuilder('i')
                        ->join('i.product', 'p')
                        ->where('p = :product')
                        ->setParameter('product', $productOption->getProduct());
                },
                'choice_label' => 'title',
            ]
        )->add(
            'values',
            Select2EntityType::class,
            [
                'multiple' => true,
                'remote_route' => 'backend_ajax',
                'class' => ProductOptionValue::class,
                'primary_key' => 'id',
                'text_property' => 'title',
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => false,
                'allow_add' => false,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 10_000,
                'language' => Locale::workingLocale(),
                'label' => 'lbl.Values',
                'action' => 'AutoCompleteProductOptionValue',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductOptionValueDependencyDataTransferObject::class,
                'product_option' => ProductOption::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'dependencies';
    }
}
