<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Brand\Command\UpdateBrand;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Commerce categories.
 */
class SequenceBrands extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the brand repository.
         */
        $brandRepository = $this->get('commerce.repository.brand');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            // update sequence
            if ($brand = $brandRepository->findOneByIdAndLocale($id, Locale::workingLocale())) {
                $updateBrand = new UpdateBrand($brand);
                $updateBrand->sequence = $i + 1;

                $this->get('command_bus')->handle($updateBrand);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
