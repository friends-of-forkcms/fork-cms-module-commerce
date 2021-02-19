<?php

namespace Backend\Modules\Catalog\Domain\Category;

use Backend\Core\Engine\DataGridDatabase;
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
    public function __construct(Locale $locale, ?Category $category)
    {
        if ($category) {
            parent::__construct(
                'SELECT c.id, c.title, COUNT(i.id) AS num_products, c.sequence
		 FROM catalog_categories AS c
		 LEFT OUTER JOIN catalog_products AS i
			ON c.id = i.category_id
			AND i.language = c.language
		 WHERE c.parent_id = :category AND c.language = :language
		 GROUP BY c.id',
                ['category' => $category->getId(), 'language' => $locale]
            );

            $this->setURL('&amp;category=' . $category->getId(), true);
        } else {
            parent::__construct(
                'SELECT c.id, c.title, COUNT(i.id) AS num_products, c.sequence
		 FROM catalog_categories AS c
		 LEFT OUTER JOIN catalog_products AS i
			ON c.id = i.category_id
			AND i.language = c.language
		 WHERE c.language = :language
		 GROUP BY c.id',
                ['language' => $locale]
            );
        }

        // sequence
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(array('data-action' => 'SequenceCategories'));

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditCategory')) {
            $editUrl = Model::createUrlForAction('EditCategory', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale, ?Category $category): string
    {
        return (new self($locale, $category))->getContent();
    }
}
