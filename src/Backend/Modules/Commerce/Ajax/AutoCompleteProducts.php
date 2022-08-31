<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Product\Product;
use Symfony\Component\HttpFoundation\Response;

class AutoCompleteProducts extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $entityManager = $this->get('commerce.repository.product');
        $page = $this->getRequest()->query->get('page');

        if ($page < 1) {
            $page = 1;
        }

        /**
         * @var Product[] $products
         */
        $products = $entityManager->findForAutoComplete(
            Locale::workingLocale(),
            $this->getRequest()->request->get('q'),
            $this->getRequest()->query->get('excluded_id'),
            $this->getRequest()->query->get('page_limit'),
            $page
        );

        // build the return data
        $returnData = [
            'query_data' => [],
        ];

        foreach ($products as $product) {
            $returnData['query_data'][] = [
                'id' => $product->getId(),
                'text' => $product->getTitle(),
            ];
        }

        // success output
        $this->output(Response::HTTP_OK, $returnData);
    }
}
