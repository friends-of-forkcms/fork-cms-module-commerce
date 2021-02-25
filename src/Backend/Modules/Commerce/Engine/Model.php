<?php

namespace Backend\Modules\Commerce\Engine;

use Backend\Core\Engine\Model as BackendModel;
use SpoonFilter;

/**
 * In this file we store all generic functions that we will be using in the Commerce module.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Model
{
    /**
     * Delete a certain item.
     *
     * @param int $id
     */
    public static function delete($id)
    {
        BackendModel::getContainer()->get('database')->delete('commerce_products', 'id = ?', (int) $id);
        BackendModel::getContainer()->get('database')->delete('commerce_specifications_values', 'product_id = ?', (int) $id);
    }

    /**
     * Delete all spam.
     */
    public static function deleteCompletedOrders()
    {
        $db = BackendModel::getContainer()->get('database');

        // get ids
        $itemIds = (array) $db->getColumn('SELECT i.id
			 FROM commerce_orders AS i
			 WHERE status = ?', ['completed']);

        // update record
        $db->delete('commerce_orders', 'status = ?', ['completed']);

        // invalidate the cache for commerce
        BackendModel::invalidateFrontendCache('commerce', BL::getWorkingLanguage());
    }

    /**
     * Checks if a certain item exists.
     *
     * @param int $id
     *
     * @return bool
     */
    public static function exists($id)
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM commerce_products AS i
			 WHERE i.id = ?
			 LIMIT 1', [(int) $id]);
    }

    /**
     * Checks if image exists.
     *
     * @param int $id
     *
     * @return bool
     */
    public static function existsImage($id)
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM commerce_images AS a
			 WHERE a.id = ?', [(int) $id]);
    }

    /**
     * Checks if file exists.
     *
     * @param int $id
     *
     * @return bool
     */
    public static function existsFile($id)
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM commerce_files AS a
			 WHERE a.id = ?', [(int) $id]);
    }

    /**
     * Fetches a certain item.
     *
     * @param int $id
     *
     * @return array
     */
    public static function get($id)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM commerce_products AS i
			 WHERE i.id = ?', [(int) $id]);
    }

    /**
     * Fetches a all items.
     *
     * @param int $id
     *
     * @return array
     */
    public static function getAll()
    {
        $db = BackendModel::getContainer()->get('database');

        return (array) $db->getPairs('SELECT i.id, i.title
			 FROM commerce_products AS i
			 WHERE i.language = ?
			 GROUP BY i.id', [BL::getWorkingLanguage()]);
    }

    /**
     * Get all the categories.
     *
     * @param bool [optional] $includeCount
     *
     * @return array
     */
    public static function getCategories($includeCount = false)
    {
        $db = BackendModel::getContainer()->get('database');

        if ($includeCount) {
            $allCategories = (array) $db->getRecords('SELECT i.id, i.parent_id, CONCAT(i.title, " (", COUNT(p.category_id) ,")") AS title
				 FROM commerce_categories AS i
				 LEFT OUTER JOIN commerce_products AS p ON i.id = p.category_id AND i.language = p.language
				 WHERE i.language = ?
				 GROUP BY i.id
				 ORDER BY i.sequence', [BL::getWorkingLanguage()]);

            $tree = [];

            $categoryTree = self::buildTree($tree, $allCategories);
            $categoryTree = ['no_category' => ucfirst(BL::getLabel('None'))] + $categoryTree;

            return $categoryTree;
        }
    }

    /**
     * Build the category tree.
     *
     * @param $tree
     * @param int $parentId
     * @param int $level
     *
     * @return array
     */
    public static function buildTree(array &$tree, array $categories, $parentId = 0, $level = 0)
    {
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $tree[$category['id']] = str_repeat('-', $level).$category['title'];

                ++$level;
                $children = self::buildTree($tree, $categories, $category['id'], $level);
                --$level;
            }
        }

        return $tree;
    }

    /**
     * Get all data for a given id.
     *
     * @param int $id The Id of the comment to fetch?
     *
     * @return array
     */
    public static function getComment($id)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on,
			 p.id AS product_id, p.title AS product_title, m.url AS product_url
			 FROM commerce_comments AS i
			 INNER JOIN commerce_products AS p ON i.product_id = p.id AND i.language = p.language
			 INNER JOIN meta AS m ON p.meta_id = m.id
			 WHERE i.id = ?
			 LIMIT 1', [(int) $id]);
    }

    /**
     * Get all data for a given id.
     *
     * @param int $id The Id of the order to fetch?
     *
     * @return array
     */
    public static function getOrder($id)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*, UNIX_TIMESTAMP(i.date) AS ordered_on,
			 p.amount AS amount_of_product, c.title AS product_title
			 FROM commerce_orders AS i
			 INNER JOIN commerce_orders_values AS p ON i.id = p.order_id
			 INNER JOIN commerce_products AS c ON p.product_id = c.id
			 WHERE i.id = ?
			 LIMIT 1', [(int) $id]);
    }

    /**
     * Get multiple comments at once.
     *
     * @param array $ids the id(s) of the comment(s)
     *
     * @return array
     */
    public static function getComments(array $ids)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecords('SELECT *
			 FROM commerce_comments AS i
			 WHERE i.id IN ('.implode(', ', array_fill(0, count($ids), '?')).')', $ids);
    }

    /**
     * Fetch a category.
     *
     * @param int $id
     *
     * @return array
     */
    public static function getCategory($id)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM commerce_categories AS i
			 WHERE i.id = ? AND i.language = ?', [(int) $id, BL::getWorkingLanguage()]);
    }

    /**
     * Fetch a category.
     *
     * @param int $id
     *
     * @return array
     */
    public static function getBrand($id)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM commerce_brands AS i
			 WHERE i.id = ?', [(int) $id]);
    }

    /**
     * Retrieve the unique URL for an item.
     *
     * @param string         $url
     * @param int [optional] $id  The id of the item to ignore
     *
     * @return string
     */
    public static function getURL($url, $id = null)
    {
        $url = SpoonFilter::urlise((string) $url);
        $db = BackendModel::getContainer()->get('database');

        // new item
        if ($id === null) {
            // already exists
            if ((bool) $db->getVar('SELECT 1
				 FROM commerce_products AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1', [BL::getWorkingLanguage(), $url])
            ) {
                $url = BackendModel::addNumber($url);

                return self::getURL($url);
            }
        } else {
            // current item should be excluded
            // already exists
            if ((bool) $db->getVar('SELECT 1
				 FROM commerce_products AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1', [BL::getWorkingLanguage(), $url, $id])
            ) {
                $url = BackendModel::addNumber($url);

                return self::getURL($url, $id);
            }
        }

        return $url;
    }

    /**
     * Insert an item in the database.
     *
     * @return int
     */
    public static function insert(array $item)
    {
        $item['created_on'] = BackendModel::getUTCDate();
        $item['edited_on'] = BackendModel::getUTCDate();

        return (int) BackendModel::getContainer()->get('database')->insert('commerce_products', $item);
    }

    /**
     * Insert a image in the database.
     *
     * @param string $item
     *
     * @return int
     */
    private static function insertImage($item)
    {
        return (int) BackendModel::getContainer()->get('database')->insert('commerce_images', $item);
    }

    /**
     * Insert a file in the database.
     *
     * @param string $item
     *
     * @return int
     */
    private static function insertFile($item)
    {
        return (int) BackendModel::getContainer()->get('database')->insert('commerce_files', $item);
    }

    /**
     * Save or update a image.
     *
     * @return int
     */
    public static function saveImage(array $item)
    {
        // update image
        if (isset($item['id']) && self::existsImage($item['id'])) {
            self::updateImage($item);
        } else {
            // insert image
            $item['id'] = self::insertImage($item);
        }

        BackendModel::invalidateFrontendCache('productsCache');

        return (int) $item['id'];
    }

    /**
     * Save or update a file.
     *
     * @return int
     */
    public static function saveFile(array $item)
    {
        // update file
        if (isset($item['id']) && self::existsFile($item['id'])) {
            self::updateFile($item);
        } else {
            // insert file
            $item['id'] = self::insertFile($item);
        }

        BackendModel::invalidateFrontendCache('productsCache');

        return (int) $item['id'];
    }

    /**
     * Updates an item.
     */
    public static function update(array $item)
    {
        $item['edited_on'] = BackendModel::getUTCDate();
        BackendModel::getContainer()->get('database')->update('commerce_products', $item, 'id = ?', (int) $item['id']);
    }

    /**
     * Update a certain category.
     */
    public static function updateCategory(array $item)
    {
        $item['edited_on'] = BackendModel::getUTCDate();
        BackendModel::getContainer()->get('database')->update('commerce_categories', $item, 'id = ?', [$item['id']]);

        // update extra
        BackendModel::updateExtra(
            $item['extra_id'],
            'data',
            [
                'id' => $item['id'],
                'extra_label' => BL::getLabel('Category').' '.$item['title'],
                'language' => $item['language'],
                'edit_url' => BackendModel::createURLForAction('EditCategory').'&id='.$item['id'],
            ]
        );
    }

    /**
     * @return int
     */
    public static function updateImage(array $item)
    {
        BackendModel::invalidateFrontendCache('productsCache');

        return (int) BackendModel::getContainer()->get('database')->update('commerce_images', $item, 'id = ?', [$item['id']]);
    }

    /**
     * @return int
     */
    public static function updateFile(array $item)
    {
        BackendModel::invalidateFrontendCache('productsCache');

        return (int) BackendModel::getContainer()->get('database')->update('commerce_files', $item, 'id = ?', [$item['id']]);
    }

    /**
     * @return int
     */
    public static function updateVideo(array $item)
    {
        BackendModel::invalidateFrontendCache('productsCache');

        return (int) BackendModel::getContainer()->get('database')->update('commerce_videos', $item, 'id = ?', [$item['id']]);
    }
}
