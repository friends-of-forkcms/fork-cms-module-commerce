<?php

namespace Backend\Modules\Commerce\Domain\Country;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
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
            'SELECT c.id, c.name, c.iso as iso_code
					 FROM commerce_countries AS c
					 WHERE c.language = :language',
            ['language' => $locale]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditCountry')) {
            $editUrl = Model::createUrlForAction('EditCountry', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('name', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }
}
