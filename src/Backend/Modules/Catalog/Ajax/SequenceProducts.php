<?php

namespace Backend\Modules\Catalog\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Product\Command\UpdateProduct;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Catalog products
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class SequenceProducts extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the product repository
         */
        $productRepository = $this->get('catalog.repository.product');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));
        $offset = $this->getRequest()->request->getInt('currentOffset', 0);

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {

            // update sequence
            if ($product = $productRepository->findOneByIdAndLocale($id, Locale::workingLocale())) {
                $updateProduct = new UpdateProduct($product);
                $updateProduct->sequence = $offset + $i + 1;

                $this->get('command_bus')->handle($updateProduct);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
