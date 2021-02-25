<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Core\Engine\Base\Action as BackendBaseAction;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\Product\Command\CreateProduct;
use Backend\Modules\Commerce\Domain\Product\Event\Created;
use Backend\Modules\Commerce\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Backend\Modules\Commerce\Domain\ProductOption\Command\CreateProductOption;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\Command\CreateProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\Command\UpdateProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroupMediaItem\MediaGroupMediaItem;
use Common\Doctrine\Entity\Meta;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

class Copy extends BackendBaseAction
{
    /**
     * @var array
     */
    private $productOptionIds = [];

    /**
     * @var array
     */
    private $productOptionValueIds = [];

    /**
     * @var array
     */
    private $dependenciesCache = [];

    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $copyForm = $this->get('form.factory')->createNamed(
            'copy',
            DeleteType::class,
            null,
            ['module' => $this->getModule()]
        );

        $copyForm->handleRequest($this->getRequest());
        if (!$copyForm->isSubmitted() || !$copyForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }

        $copyFormData = $copyForm->getData();

        $product = $this->getProduct((int)$copyFormData['id']);
        $createProduct = new CreateProduct(clone $product);
        $createProduct->copy();
        $createProduct->title = $createProduct->title . ' (' . ucfirst(Language::lbl('Copy')) . ')';
        $createProduct->meta = $this->copyMeta($product->getMeta());
        $createProduct->images = $this->copyMediaGroup($product->getImages());
        $createProduct->downloads = $this->copyMediaGroup($product->getDownloads());
        $createProduct->dimensions = $this->copyCollection($product->getDimensions());
        $createProduct->dimension_notifications = $this->copyCollection($product->getDimensionNotifications());
        $createProduct->specials = $this->copyCollection($product->getSpecials());

        $this->get('command_bus')->handle($createProduct);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createProduct->getProductEntity())
        );

        $this->copyProductOptions($product->getProductOptions(), $createProduct->getProductEntity());
        $this->copyDependencies();

        $this->redirect(
            $this->getBackLink([
                'id' => $createProduct->getProductEntity()->getId(),
                'report' => 'edited',
                'var' => $createProduct->getProductEntity()->getTitle(),
            ])
        );
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Edit',
            null,
            null,
            $parameters
        );
    }

    private function copyMeta(Meta $meta)
    {
        $url = $this->getProductRepository()->getUrl(
            $meta->getUrl(),
            Locale::workingLocale(),
            null
        );

        return new Meta(
            $meta->getKeywords(),
            $meta->isKeywordsOverwrite(),
            $meta->getDescription(),
            $meta->isDescriptionOverwrite(),
            $meta->getTitle(),
            $meta->isTitleOverwrite(),
            $url,
            $meta->isUrlOverwrite(),
            $meta->getCustom(),
            $meta->getSEOFollow(),
            $meta->getSEOIndex(),
            $meta->getData()
        );
    }

    private function copyMediaGroup(?MediaGroup $mediaGroup)
    {
        if (!$mediaGroup) {
            return null;
        }

        $newMediaGroup = MediaGroup::create($mediaGroup->getType());
        $sequence = 0;
        foreach ($mediaGroup->getConnectedMediaItems() as $connectedMediaItem) {

            $mediaGroup->addConnectedItem(MediaGroupMediaItem::create(
                $newMediaGroup,
                $connectedMediaItem,
                $sequence++
            ));
        }

        return $newMediaGroup;
    }

    private function copyCollection($collection)
    {
        $newCollection = new ArrayCollection();

        foreach ($collection as $item) {
            $newItem = clone $item;
            $newCollection->add($newItem);
        }

        return $newCollection;
    }

    /**
     * @param ProductOption[] $productOptions
     * @param Product $product
     * @param ProductOptionValue $parentProductOptionValue
     */
    private function copyProductOptions($productOptions, Product $product, ProductOptionValue $parentProductOptionValue = null)
    {
        foreach ($productOptions as $productOption) {
            $createProductOption = new CreateProductOption($productOption);
            $createProductOption->copy();
            $createProductOption->product = $product;
            $createProductOption->dimension_notifications = new ArrayCollection();

            if ($parentProductOptionValue) {
                $createProductOption->parent_product_option_value = $parentProductOptionValue;
            }

            foreach ($productOption->getDimensionNotifications() as $dimensionNotification) {
                $dimensionNotification = clone $dimensionNotification;
                $dimensionNotification->setProduct($product);

                $createProductOption->addDimensionNotification($dimensionNotification);
            }

            $this->get('command_bus')->handle($createProductOption);

            $this->productOptionIds[$productOption->getId()] = $createProductOption->getProductOptionEntity();

            $this->copyProductOptionValues($productOption->getProductOptionValues(), $createProductOption->getProductOptionEntity(), $product);
        }
    }

    /**
     * @param ProductOptionValue[] $productOptionValues
     * @param ProductOption $productOption
     * @param Product $product
     */
    private function copyProductOptionValues($productOptionValues, $productOption, $product)
    {
        foreach ($productOptionValues as $productOptionValue) {
            $createProductOptionValue = new CreateProductOptionValue($productOptionValue);
            $createProductOptionValue->copy();
            $createProductOptionValue->productOption = $productOption;
            $createProductOptionValue->image = $this->copyMediaGroup($productOptionValue->getImage());
            $createProductOptionValue->dependencies = new ArrayCollection();

            $this->cacheDependencies($productOptionValue->getDependencies());

            $this->get('command_bus')->handle($createProductOptionValue);

            $this->productOptionValueIds[$productOptionValue->getId()] = $createProductOptionValue->getProductOptionValueEntity();

            $this->copyProductOptions($productOptionValue->getProductOptions(), $product, $createProductOptionValue->getProductOptionValueEntity());
        }
    }

    /**
     * The dependencies are cached for later usage
     *
     * @param PersistentCollection $dependencies
     */
    private function cacheDependencies(PersistentCollection $dependencies)
    {
        if ($dependencies->isEmpty()) {
            return;
        }

        $this->dependenciesCache[] = $dependencies;
    }

    private function copyDependencies()
    {
        /**
         * @var PersistentCollection $dependencies
         */
        foreach ($this->dependenciesCache as $dependencies) {
            $updateProductOptionValue = new UpdateProductOptionValue($this->productOptionValueIds[$dependencies->getOwner()->getId()]);

            foreach ($dependencies as $dependency) {
                $updateProductOptionValue->dependencies->add($this->productOptionValueIds[$dependency->getId()]);
            }

            $this->get('command_bus')->handle($updateProductOptionValue);
        }
    }

    /**
     * @param int $id
     * @return Product
     * @throws \Common\Exception\RedirectException
     */
    private function getProduct(int $id): Product
    {
        try {
            return $this->getProductRepository()->findOneByIdAndLocale(
                $id,
                Locale::workingLocale()
            );
        } catch (ProductNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getProductRepository(): ProductRepository
    {
        return $this->get('commerce.repository.product');
    }
}
