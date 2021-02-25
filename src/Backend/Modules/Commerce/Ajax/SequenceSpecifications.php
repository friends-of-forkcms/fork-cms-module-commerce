<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Specification\Command\UpdateSpecification;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of specification values
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class SequenceSpecifications extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the specifications repository
         */
        $specificationRepository = $this->get('commerce.repository.specification');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {

            // update sequence
            if ($vat = $specificationRepository->findOneByIdAndLocale($id, Locale::workingLocale())) {
                $updateSequence = new UpdateSpecification($vat);
                $updateSequence->sequence = $i + 1;

                $this->get('command_bus')->handle($updateSequence);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
