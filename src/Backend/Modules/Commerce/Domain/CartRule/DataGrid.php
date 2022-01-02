<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
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
            'SELECT c.id, c.title, c.code as discountCode, c.quantity, c.tillDate as `till`
             FROM commerce_cart_rules AS c
             WHERE c.language = :language
             GROUP BY c.id
             ORDER BY c.title ASC',
            ['language' => $locale]
        );

        $this->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);
        $this->setColumnFunction('htmlspecialchars', ['[discountCode]'], 'discountCode', false);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditCartRule')) {
            $editUrl = Model::createUrlForAction('EditCartRule', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }
}
