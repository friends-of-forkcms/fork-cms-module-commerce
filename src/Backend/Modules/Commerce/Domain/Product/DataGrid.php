<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale, ?Category $category, ?string $sku, int $offset = 0)
    {
        $query = 'SELECT
                    i.id,
                    i.title AS title,
                    i.sku,
                    i.category_id,
                    b.title AS brand,
                    i.price,
                    i.stock,
                    i.sequence,
                    i.hidden,
                    i.sequence as `sort_order`
                FROM commerce_products AS i
                LEFT JOIN commerce_brands AS b ON b.id = i.brand_id
                WHERE i.language = :language';

        $parameters = [
            'language' => $locale
        ];

        if ($category) {
            $query = 'SELECT
                        i.id,
                        i.title AS title,
                        i.sku,
                        i.category_id,
                        b.title AS brand,
                        i.price,
                        i.stock,
                        i.sequence,
                        i.hidden,
                        i.sequence as `sort_order`
                    FROM commerce_products AS i
                    LEFT JOIN commerce_brands AS b ON b.id = i.brand_id
                    WHERE i.language = :language AND i.category_id = :category';

            $parameters['category'] = $category->getId();
        }

        if ($sku) {
            $query .= ' AND i.`sku` LIKE :sku';
            $parameters['sku'] = '%' . $sku .'%';
        }

        parent::__construct($query, $parameters);

        $this->enableSequenceByDragAndDrop();
        $this->setPaging(true);
        $this->setAttributes(
            [
                'data-action' => 'SequenceProducts',
                'data-extra-params' => '{\'currentOffset\' : '. $offset .'}',
            ]
        );

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(array('id' => 'row-[id]'));
        $this->setColumnsHidden(array('sequence'));
        $this->setColumnFunction([self::class, 'categoryName'], ['[category_id]'], 'category_id');
        $this->setHeaderLabels(
            [
                'category_id' => ucfirst(Language::lbl('Category')),
                'sku' => ucfirst(Language::lbl('ArticleNumber')),
                'sort_order' => ucfirst(Language::lbl('Sequence')),
            ]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('Edit')) {
            $editUrl = Model::createUrlForAction('Edit', null, null,
                ['id' => '[id]', 'category' => $category ? $category->getId() : null], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale, ?Category $category, ?string $sku, int $offset = 0): string
    {
        return (new self($locale, $category, $sku, $offset))->getContent();
    }

    public static function categoryName(int $categoryId): string
    {
        /**
         * @var CategoryRepository $categoryRepository
         */
        $categoryRepository = Model::get('commerce.repository.category');

        $category = $categoryRepository->findOneByIdAndLocale($categoryId, Locale::workingLocale());

        return self::generateCategoryName($category);
    }

    private static function generateCategoryName(Category $category, $separator = ' - ', $first = true): string
    {
        $name = null;
        if ($category->getParent()) {
            $name = self::generateCategoryName($category->getParent(), $separator, false) . $separator;
        }

        if ($first) {
            $name .= '<strong>' . $category->getTitle() . '</strong>';
        } else {
            $name .= $category->getTitle();
        }

        return $name;
    }
}
