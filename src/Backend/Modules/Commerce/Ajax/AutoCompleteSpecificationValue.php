<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Commerce categories.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AutoCompleteSpecificationValue extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $page = $this->getRequest()->query->get('page');

        if ($page < 1) {
            $page = 1;
        }

        /**
         * @var SpecificationValue[] $specificationValues
         */
        $specificationValues = $this->getSpecificationValueRepository()->findForAutoComplete(
            $this->getRequest()->request->get('q', ''),
            $this->getRequest()->request->get('parent'),
            $this->getRequest()->query->get('page_limit'),
            $page
        );

        // build the return data
        $returnData = [
            'query_data' => [],
        ];

        foreach ($specificationValues as $specificationValue) {
            $returnData['query_data'][] = [
                'id' => $specificationValue->getId(),
                'text' => $specificationValue->getValue(),
            ];
        }

        // success output
        $this->output(Response::HTTP_OK, $returnData);
    }

    private function getSpecificationValueRepository(): SpecificationValueRepository
    {
        return $this->get('commerce.repository.specification_value');
    }
}
