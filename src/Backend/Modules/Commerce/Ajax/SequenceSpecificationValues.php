<?php

namespace Backend\Modules\Commerce\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Commerce\Domain\SpecificationValue\Command\UpdateSpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of specification values.
 */
class SequenceSpecificationValues extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the specification values repository.
         *
         * @var SpecificationValueRepository $specificationValueRepository
         */
        $specificationValueRepository = $this->get('commerce.repository.specification_value');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            // update sequence
            if ($item = $specificationValueRepository->findOneById($id)) {
                $updateSequence = new UpdateSpecificationValue($item);
                $updateSequence->sequence = $i + 1;

                $this->get('command_bus')->handle($updateSequence);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
