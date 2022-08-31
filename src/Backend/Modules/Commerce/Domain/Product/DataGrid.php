<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Money\Currency;
use Money\Money;
use Tbbc\MoneyBundle\Formatter\MoneyFormatter;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(
        Locale $locale,
        ?Category $category,
        ?Brand $brand,
        ?string $searchQuery,
        int $offset = 0
    ) {
        $whereFilter = ['1=1'];
        $parameters = [];

        if ($category !== null) {
            $whereFilter[] = 'i.categoryId = :category';
            $parameters['category'] = $category->getId();
        }

        if ($brand !== null) {
            $whereFilter[] = 'b.id = :brand';
            $parameters['brand'] = $brand->getId();
        }

        // Search product title, SKU, barcode
        // Note: use MATCH AGAINST once MySQL 8 is more common
        if (!empty($searchQuery)) {
            $whereFilter[] = '(i.title LIKE :searchQuery OR i.sku LIKE :searchQuery OR i.barcode LIKE :searchQuery)';
            $parameters['searchQuery'] = '%' . $searchQuery . '%';
        }

        $whereFilterString = implode(' AND ', $whereFilter);
        $query = "
            SELECT
                i.id,
                i.title AS title,
                b.title AS brand,
                i.sku,
                i.categoryId,
                IF(i.fromStock = 1, i.stock, 'N/A') AS stock,
                i.priceAmount AS price,
                i.priceCurrencyCode,
                i.sequence,
                i.hidden,
                mi.url AS imageUrl,
                mi.shardingFolderName AS imageShardingFolderName
            FROM commerce_products AS i
            LEFT JOIN commerce_brands AS b ON b.id = i.brandId
            LEFT JOIN MediaGroupMediaItem AS mgmi ON mgmi.mediaGroupId = i.imageGroupId AND mgmi.sequence = 0
            LEFT JOIN MediaItem AS mi ON mgmi.mediaItemId = mi.id
            WHERE i.language = :language AND $whereFilterString
            GROUP BY 1
        ";
        $parameters['language'] = $locale;

        parent::__construct($query, $parameters);

        // Set datagrid options
        $this->enableSequenceByDragAndDrop();
        $this->setPaging(true);
        $this->setAttributes(
            [
                'data-action' => 'SequenceProducts',
                'data-extra-params' => '{\'currentOffset\' : ' . $offset . '}',
            ]
        );

        // Escape values
        $this->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);
        $this->setColumnFunction('htmlspecialchars', ['[sku]'], 'sku', false);
        $this->setColumnFunction('htmlspecialchars', ['[brand]'], 'brand', false);

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(['id' => 'row-[id]']);
        $this->setColumnsHidden(['sequence', 'priceCurrencyCode']);
        $this->setColumnFunction([self::class, 'categoryName'], ['[categoryId]'], 'categoryId');
        $this->setHeaderLabels(
            [
                'categoryId' => ucfirst(Language::lbl('Category')),
                'sku' => ucfirst(Language::lbl('ArticleNumber')),
            ]
        );

        // Format price column properly
        $this->setColumnFunction([self::class, 'getFormattedMoney'], ['[price]', '[priceCurrencyCode]'], 'price', true);

        // Check if this action is allowed
        $editUrl = null;
        if (BackendAuthentication::isAllowedAction('Edit')) {
            $editUrl = Model::createUrlForAction(
                'Edit',
                null,
                null,
                ['id' => '[id]', 'category' => $category ? $category->getId() : null],
                false
            );
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }

        // Add thumbnail and render it first in the datagrid
        $this->addColumn('thumb', '');
        $this->setColumnsHidden(['imageUrl', 'imageShardingFolderName']);
        $this->setColumnFunction(
            [BackendDataGridFunctions::class, 'showImage'],
            [
                Model::get('media_library.storage.local')->getWebDir() . '/[imageShardingFolderName]',
                '[imageUrl]',
                '[imageUrl]',
                $editUrl,
                50,
                50,
                'product_thumbnail_square',
            ],
            'thumb',
            true
        );
        $this->setColumnsSequence(['dragAndDropHandle', 'sortHandle', 'sequence', 'thumb']);
    }

    public static function getHtml(
        Locale $locale,
        ?Category $category,
        ?Brand $brand,
        ?string $searchQuery,
        int $offset = 0
    ): string {
        return (new self($locale, $category, $brand, $searchQuery, $offset))->getContent();
    }

    public static function categoryName(int $categoryId): string
    {
        /** @var CategoryRepository $categoryRepository */
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

    public static function getFormattedMoney(int $amount, string $currencyCode): string
    {
        $money = new Money($amount, new Currency($currencyCode));

        return (new MoneyFormatter())->localizedFormatMoney($money);
    }
}
