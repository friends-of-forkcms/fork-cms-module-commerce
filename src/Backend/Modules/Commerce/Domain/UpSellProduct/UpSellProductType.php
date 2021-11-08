<?php

namespace Backend\Modules\Commerce\Domain\UpSellProduct;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Product\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class UpSellProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('upSellProduct', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'backend_ajax',
                'remote_params' => [
                    'excluded_id' => ($options['product'] ? $options['product']->getId() : null),
                ],
                'class' => Product::class,
                'primary_key' => 'id',
                'text_property' => 'getTitle',
                'minimum_input_length' => 3,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => false,
                'cache_timeout' => 60_000, // if 'cache' is true
                'language' => Locale::workingLocale(),
                'label' => 'lbl.Product',
                'action' => 'AutoCompleteProducts',
            ])
            ->add('sequence', HiddenType::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => UpSellProduct::class,
                'product' => null,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'up_sell_product';
    }
}
