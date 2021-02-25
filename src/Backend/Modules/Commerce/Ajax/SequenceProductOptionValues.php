<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Commerce\Domain\ProductOptionValue\Command\UpdateProductOptionValue;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Commerce product options.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class SequenceProductOptionValues extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the category repository.
         */
        $productOptionValueRepository = $this->get('commerce.repository.product_option_value');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            // update sequence
            if ($productOptionValue = $productOptionValueRepository->findOneById($id)) {
                $updateProductOption = new UpdateProductOptionValue($productOptionValue);
                $updateProductOption->sequence = $i + 1;

                $this->get('command_bus')->handle($updateProductOption);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
