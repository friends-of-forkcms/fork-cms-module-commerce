<?php

namespace Backend\Modules\Catalog\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\Command\UpdateCategory;
use Backend\Modules\Catalog\Domain\Product\Command\UpdateProduct;
use Backend\Modules\Catalog\Domain\ProductOption\Command\UpdateProductOption;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Catalog product options
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class SequenceProductOptions extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the category repository
         */
        $productOptionRepository = $this->get('catalog.repository.product_option');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {

            // update sequence
            if ($productOption = $productOptionRepository->findOneById($id)) {
                $updateProductOption = new UpdateProductOption($productOption);
                $updateProductOption->sequence = $i + 1;

                $this->get('command_bus')->handle($updateProductOption);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
