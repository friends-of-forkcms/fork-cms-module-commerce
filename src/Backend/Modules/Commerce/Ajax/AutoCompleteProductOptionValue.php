<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValueRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Commerce categories.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AutoCompleteProductOptionValue extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $page = $this->getRequest()->query->get('page');
        $pageLimit = (int) $this->getRequest()->query->get('page_limit');
        $parentId = $this->getRequest()->request->get('parent');

        if ($parentId === null) {
            $this->output(Response::HTTP_UNPROCESSABLE_ENTITY, 'Parent id not set, try again!');

            return;
        }

        if ($page < 1) {
            $page = 1;
        }

        if ($pageLimit < 1) {
            $pageLimit = 10;
        }

        /**
         * @var ProductOptionValue[] $productOptionValues
         */
        $productOptionValues = $this->getProductOptionValueRepository()->findForAutoComplete(
            $this->getRequest()->request->get('q', ''),
            $parentId,
            $pageLimit,
            $page
        );

        // build the return data
        $returnData = [
            'query_data' => [],
        ];

        foreach ($productOptionValues as $specificationValue) {
            $returnData['query_data'][] = [
                'id' => $specificationValue->getId(),
                'text' => $specificationValue->getTitle(),
            ];
        }

        // success output
        $this->output(Response::HTTP_OK, $returnData);
    }

    private function getProductOptionValueRepository(): ProductOptionValueRepository
    {
        return $this->get('commerce.repository.product_option_value');
    }
}
