<?php

namespace Backend\Modules\Catalog\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\Command\UpdateCategory;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Catalog categories
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AutoCompleteSpecificationValue extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $entityManager = $this->get('catalog.repository.specification_value');
        $page          = $this->getRequest()->query->get('page');

        if ($page < 1) {
            $page = 1;
        }

        /**
         * @var SpecificationValue[] $specificationValues
         */
        $specificationValues = $entityManager->findForAutoComplete(
            $this->getRequest()->request->get('q', ''),
            $this->getRequest()->request->get('parent'),
            $this->getRequest()->query->get('page_limit'),
            $page
        );

        // build the return data
        $returnData = [
            'query_data' => []
        ];

        foreach ($specificationValues as $specificationValue) {
            $returnData['query_data'][] = [
                'id'   => $specificationValue->getId(),
                'text' => $specificationValue->getValue(),
            ];
        }

        // success output
        $this->output(Response::HTTP_OK, $returnData);
    }
}
