<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Modules\Catalog\Domain\Vat\Vat;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValueType extends AbstractType
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
            'price',
            MoneyType::class,
            [
                'required' => false,
                'label' => 'lbl.Price',
            ]
        )->add(
            'vat',
            EntityType::class,
            [
                'label'         => 'lbl.Vat',
                'class'         => Vat::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.sequence', 'ASC');
                },
                'choice_label'  => 'title'
            ]
        )->add(
            'default_value',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'lbl.DefaultValue',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductOptionValueDataTransferObject::class
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'product_option_value';
    }
}
