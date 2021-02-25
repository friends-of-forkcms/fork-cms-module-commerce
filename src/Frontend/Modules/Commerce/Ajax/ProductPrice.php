<?php

namespace Frontend\Modules\Commerce\Ajax;

use Backend\Modules\Commerce\Domain\Product\AddToCartDataTransferObject;
use Backend\Modules\Commerce\Domain\Product\AddToCartType;
use Backend\Modules\Commerce\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Frontend\Core\Engine\TemplateModifiers;
use Frontend\Core\Language\Locale;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\Response;

class ProductPrice extends DimensionCalculator
{
    /**
     * @var array
     */
    private $notifications = [];

    /**
     * @var array
     */
    private $productOptions = [];

    public function execute(): void
    {
        // Product must be set
        if (!$this->getRequest()->request->has('product')) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }

        $productData = $this->getRequest()->request->get('product');

        if (!is_array($productData)) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }

        if (!array_key_exists('id', $productData) || !array_key_exists('amount', $productData)) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }

        // Retrieve our product
        try {
            $product = $this->getProductRepository()->findOneActiveByIdAndLocale($productData['id'], Locale::frontendLanguage());
        } catch (ProductNotFound $e) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }

        // Get the form on which we are going to validate our stuff
        $form = $this->getForm($product);
        $form->handleRequest($this->getRequest());
        $this->data = $form->getData();

        if ($this->data->amount < $product->getOrderQuantity()) {
            $this->data->amount = $product->getOrderQuantity();
        }

        $returnData = [
            'product' => [
                'amount' => $this->data->amount,
                'width' => $this->data->width,
                'height' => $this->data->height,
                'usesDimensions' => $product->usesDimensions(),
            ],
            'options' => [],
            'allowAddToCart' => ($form->isSubmitted() && $form->isValid()),
            'errors' => $this->buildErrors($form->getErrors(true, true)),
        ];

        // Store current width and height
        $this->addWidth($this->data->width);
        $this->addHeight($this->data->height);

        if ($product->usesDimensions()) {
            // Add extra product dimensions
            $this->addWidth($product->getExtraProductionWidth());
            $this->addHeight($product->getExtraProductionHeight());

            // Parse any given dimensions
            $this->parseProductOptionsDimension($product->getProductOptions());

            $dimension = $this->getProductDimensionRepository()->findByProductAndDimensions(
                $product,
                $this->getWidth(),
                $this->getHeight()
            );

            if ($dimension) {
                $returnData['product']['price'] = $dimension->getPrice();
                $returnData['product']['vat_price'] = $dimension->getPrice();

                $this->setBasePrice($dimension->getPrice());
                $this->addTotalPrice($dimension->getPrice() + $dimension->getVatPrice());

                $this->addNotification(
                    'dimensions',
                    $product->getDimensionNotificationByDimension($this->getWidth(), $this->getHeight())
                );
            } else {
                $returnData['allowAddToCart'] = false;
            }
        } else {
            $returnData['product']['price'] = $product->getPrice();
            $returnData['product']['vat_price'] = $product->getVatPrice();

            $this->setBasePrice($product->getPrice());
            $this->addTotalPrice($product->getActivePrice());
        }

        // Parse all the options
        foreach ($product->getProductOptions() as $option) {
            $this->parseProductOption($option, $product);
        }

        // Store all information
        $returnData['options'] = $this->getProductOptions();
        $returnData['total_price'] = TemplateModifiers::formatNumber($this->getTotalPrice() * $this->data->amount, 2);
        $returnData['notifications'] = $this->notifications;

        $this->output(Response::HTTP_OK, $returnData);
    }

    /**
     * @param ProductOption $option
     * @param Product $product
     */
    private function parseProductOption(ProductOption $option, Product $product): void
    {
        $propertyName = 'option_' . $option->getId();
        $propertyNameCustomValue = $propertyName . '_custom_value';

        if (!property_exists($this->data, $propertyName)) { //  || $this->data->{$propertyName} === null
            return ;
        }

        $price = 0;
        $vatPrice = 0;
        $value = null;
        $impactType = ProductOptionValue::IMPACT_TYPE_ADD;

        if ($option->isCustomValueAllowed() && isset($this->data->{$propertyNameCustomValue})) {
            $price = $option->getCustomValuePrice();
            $vatPrice = $option->getCustomValuePrice() * $product->getVat()->getAsPercentage();
            $value = $option->getPrefix() . $this->data->{$propertyNameCustomValue} . $option->getSuffix();
        } else {
            /**
             * @var ProductOptionValue $optionValue
             */
            $optionValue = $this->data->{$propertyName};
            if ($optionValue) {
                if (!$option->isTextType()) {
                    if ($optionValue->getPercentage() > 0) {
                        $price = $this->getBasePrice() * ($optionValue->getPercentage() / 100);
                        $vatPrice = $price * $optionValue->getVat()->getAsPercentage();
                    } else {
                        $price = $optionValue->getPrice();
                        $vatPrice = $optionValue->getVatPrice();
                    }

                    $impactType = $optionValue->getImpactType();
                }

                switch ($option->getType()) {
                    case ProductOption::DISPLAY_TYPE_TEXT:
                        $value = $optionValue;
                        break;
                    case ProductOption::DISPLAY_TYPE_BETWEEN:
                        $value = $this->data->{$propertyNameCustomValue};
                        break;
                    default:
                        $value = $optionValue->getTitle();
                        break;
                }
            }
        }

        // Check for dimension notifications
        if ($product->usesDimensions()) {
            $this->addNotification(
                $propertyName,
                $option->getDimensionNotificationByDimension($this->getWidth(), $this->getHeight())
            );
        }

        // Only do extra calculations based on square unit type
        if ($option->isSquareUnitType() && $product->usesDimensions()) {
            // @TODO assumed unit is given in MM
            $square = ceil(($this->getWidth() / 100) * ($this->getHeight() / 100));

            $price = $price * $square;
            $vatPrice = $vatPrice * $square;
        }

        // Update the totals
        if ($impactType == ProductOptionValue::IMPACT_TYPE_ADD) {
            $this->addTotalPrice($price + $vatPrice);
        } else {
            $this->addTotalPrice(($price + $vatPrice) * -1);
        }

        $this->addProductOption([
            'id' => $option->getId(),
            'impact_type' => $impactType,
            'name' => $propertyName,
            'price' => $price,
            'vat_price' => $vatPrice,
            'value' => $value,
        ]);

        if ($this->data->{$propertyName} !== null && !$option->isTextType()) {
            foreach ($this->data->{$propertyName}->getProductOptions() as $productOption) {
                $this->parseProductOption($productOption, $product);
            }
        }
    }

    /**
     * @param $field
     * @param ProductDimensionNotification $dimensionNotification
     */
    private function addNotification($field, ?ProductDimensionNotification $dimensionNotification): void
    {
        if (!$dimensionNotification) {
            return;
        }

        $this->notifications[$field] = [
            'width' => $dimensionNotification->getWidth(),
            'height' => $dimensionNotification->getHeight(),
            'message' => $dimensionNotification->getMessage(),
        ];
    }

    /**
     * Convert the errors to an array which can be used in the frontend
     *
     * @param FormErrorIterator $errors
     *
     * @return array
     */
    private function buildErrors(FormErrorIterator $errors): array
    {
        $errorMessages = [];

        /**
         * @var FormError $error
         */
        foreach ($errors as $key => $error) {
            $errorMessages[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errorMessages;
    }

    /**
     * @param array $productOption
     */
    protected function addProductOption(array $productOption): void
    {
        $this->productOptions[] = $productOption;
    }

    /**
     * @return array
     */
    protected function getProductOptions(): array
    {
        return $this->productOptions;
    }
}
