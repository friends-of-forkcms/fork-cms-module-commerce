<?php

namespace Backend\Modules\Commerce\Domain\Specification;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale)
    {
        parent::__construct(
            'SELECT c.id, c.title as specification, c.filter, c.sequence
					 FROM commerce_specifications AS c
					 WHERE c.language = :language
					 GROUP BY c.id',
            ['language' => $locale]
        );

        // sequence
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(['data-action' => 'SequenceSpecifications']);

        // Add some columns
        $this->setColumnFunction(
            [new DataGridFunctions(), 'showBool'],
            ['[filter]'],
            'filter',
            true
        );

        // Overwrite header labels
        $this->setHeaderLabels(
            [
                'filter' => ucfirst(Language::lbl('UseAsFilter')),
            ]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditSpecification')) {
            $editUrl = Model::createUrlForAction('EditSpecification', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('specification', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }
}
