<?php

namespace Backend\Modules\Commerce\Domain\Settings;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('overview_num_items', NumberType::class, [
                'required' => true,
                'label' => 'lbl.ItemsPerPage',
            ])
            ->add('filters_show_more_num_items', NumberType::class, [
                'required' => true,
                'label' => 'lbl.ShowMoreAfterFilterItems',
            ])
            ->add('next_invoice_number', NumberType::class, [
                'required' => true,
                'label' => 'lbl.NextInvoiceNumber',
            ])
            ->add('products_in_widget', NumberType::class, [
                'required' => true,
                'label' => 'lbl.AmountOfProductsToDisplayInAWidget',
            ])
            ->add('google_product_categories', UrlType::class, [
                'required' => false,
                'label' => 'lbl.GoogleProductCategoriesUrl',
                'attr' => [
                    'help' => 'msg.GoogleProductCategoriesUrlHelp',
                ],
            ])
            ->add(
                $builder
                    ->create(
                        'automatic_invoice_statuses',
                        EntityType::class,
                        [
                            'required' => false,
                            'label' => 'lbl.AutomaticallyGenerateInvoiceWhenStatusBecomesOneOfThese',
                            'class' => OrderStatus::class,
                            'choice_label' => 'title',
                            'choice_value' => function (OrderStatus $entity = null) {
                                return $entity ? $entity->getId() : '';
                            },
                            'multiple' => true,
                            'expanded' => true,
                        ]
                    )
                    ->addModelTransformer(
                        new CallbackTransformer(
                            function ($statusesArray) {
                                $collection = new ArrayCollection();

                                /** @var OrderStatusRepository $orderStatusRepository */
                                $orderStatusRepository = Model::get('commerce.repository.order_status');

                                foreach ($statusesArray as $id) {
                                    try {
                                        $collection->add($orderStatusRepository->findOneById($id));
                                    } catch (OrderStatusNotFound $e) {
                                        // Skip when nothing is found
                                    }
                                }

                                return $collection;
                            },
                            function ($statusesAsCollection) {
                                $ids = [];

                                /** @var OrderStatus $item */
                                foreach ($statusesAsCollection as $item) {
                                    $ids[] = $item->getId();
                                }

                                return $ids;
                            }
                        )
                    )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => SettingsDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'settings';
    }
}
