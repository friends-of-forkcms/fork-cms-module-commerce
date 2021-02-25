<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Vat\Command\UpdateVat;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Commerce categories
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class SequenceVats extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the vat repository
         */
        $vatRepository = $this->get('commerce.repository.vat');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {

            // update sequence
            if ($vat = $vatRepository->findOneByIdAndLocale($id, Locale::workingLocale())) {
                $updateVat = new UpdateVat($vat);
                $updateVat->sequence = $i + 1;

                $this->get('command_bus')->handle($updateVat);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
