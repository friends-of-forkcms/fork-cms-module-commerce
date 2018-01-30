<?php

namespace Backend\Modules\Catalog\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\Command\UpdateCategory;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alters the sequence of Catalog categories
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class SequenceCategories extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', null));

        /**
         * get the category repository
         */
        $categoryRepository = $this->get('catalog.repository.category');

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {

            // update sequence
            if ($category = $categoryRepository->findOneByIdAndLocale($id, Locale::workingLocale())) {
                $updateCategory = new UpdateCategory($category);
                $updateCategory->sequence = $i + 1;

                $this->get('command_bus')->handle($updateCategory);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
