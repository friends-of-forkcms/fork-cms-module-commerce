<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus;

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
            'SELECT c.id, c.title, c.color
             FROM commerce_order_statuses AS c
             WHERE c.language = :language
             GROUP BY c.id
             ORDER BY c.title ASC',
            ['language' => $locale]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditOrderStatus')) {
            $editUrl = Model::createUrlForAction('EditOrderStatus', null, null, ['id' => '[id]'], false);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
            $this->setColumnFunction([self::class, 'showColorDot'], ['[title]', '[color]', $editUrl], 'title', true);
            $this->setColumnsHidden(['color']);
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }

    public static function showColorDot(string $title, ?string $color, string $editUrl): string
    {
        $color = $color ?? 'currentColor';

        return <<<HTML
<svg fill="$color" viewBox="0 0 8 8" style="width: 8px; height: 8px; margin-right: 2px;">
  <circle cx="4" cy="4" r="3"></circle>
</svg>
<a href="$editUrl" title="$title">$title</a>
HTML;
    }
}
