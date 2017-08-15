<?php

namespace Backend\Modules\Catalog\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\Command\Update;
use Backend\Modules\Catalog\Domain\Product\Product;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Catalog categories
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AutoCompleteProducts extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $entityManager = $this->get('catalog.repository.product');
        $page          = $this->getRequest()->query->get('page');

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
            'query_data' => []
        ];

        foreach ($products as $product) {
            $returnData['query_data'][] = [
                'id'   => $product->getId(),
                'text' => $product->getTitle(),
            ];
        }

        // success output
        $this->output(Response::HTTP_OK, $returnData, 'sequence updated');
    }
}
