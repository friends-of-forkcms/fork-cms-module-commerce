<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\TemplateModifiers;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale, int $status)
    {
        parent::__construct(
            'SELECT
		 	i.id, i.id AS order_nr, i.status, UNIX_TIMESTAMP(i.date) AS ordered_on,
			i.email, i.first_name AS FirstName, i.last_name AS LastName, i.total AS TotalPrice
		 FROM catalog_orders AS i
		 WHERE i.status = :status
		 GROUP BY i.id',
            ['status' => $status]
        );

        $this->setHeaderLabels(array('ordered_on' => ucfirst(Language::lbl('Date'))));

        // add the multicheckbox column
        $this->setMassActionCheckboxes('checkbox', '[id]');

        // assign column functions
        $this->setColumnFunction(array(new DataGridFunctions(), 'getTimeAgo'), '[ordered_on]', 'ordered_on', true);

        // sorting
        $this->setSortingColumns(array('ordered_on', 'order_nr'), 'ordered_on');
        $this->setSortParameter('desc');

        // hide columns
        $this->setColumnsHidden(['status']);

        switch ($status) {
            case Order::STATUS_MODERATION:
                $this->setActiveTab('tabModeration');
                break;
            case Order::STATUS_COMPLETED:
                $this->setActiveTab('tabCompleted');
                break;
        }

        // @todo add mass actions

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditOrder')) {
            $editUrl = Model::createUrlForAction('EditOrder', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale, int $status): string
    {
        return (new self($locale, $status))->getContent();
    }
}
