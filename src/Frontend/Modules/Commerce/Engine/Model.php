<?php

namespace Frontend\Modules\Commerce\Engine;

use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;

/**
 * In this file we store all generic functions that we will be using in the Commerce module.
 */
class Model
{
    /**
     * Fetches a certain item.
     *
     * @param string $url
     * @param int    $id
     *
     * @return array
     */
    public static function get($url = null, $id = null)
    {
        if (!$id) {
            $item = (array) FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
                 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
                 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
                 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url, m2.url AS category_url, m2.title AS category_title
                 FROM commerce_products AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 INNER JOIN commerce_categories AS c ON i.category_id = c.id
                 INNER JOIN meta AS m2 ON c.meta_id = m2.id
                 WHERE m.url = ? AND i.language = ?', [(string) $url, FRONTEND_LANGUAGE]);
        } else {
            $item = (array) FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
                 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
                 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
                 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url, m2.url AS category_url, m2.title AS category_title
                 FROM commerce_products AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 INNER JOIN commerce_categories AS c ON i.category_id = c.id
                 INNER JOIN meta AS m2 ON c.meta_id = m2.id
                 WHERE i.id = ? AND i.language = ?', [(int) $id, FRONTEND_LANGUAGE]);
        }

        // no results?
        if (empty($item)) {
            return [];
        }

        // create full url
        $item['full_url'] = FrontendNavigation::getURLForBlock('Commerce', 'Detail') . '/' . $item['url'];
        $item['category_full_url'] = FrontendNavigation::getURLForBlock('Commerce', 'Category') . '/' . $item['category_url'];

        // add images
        if ($images = self::getImages((int) $item['id'])) {
            // Use first image as the main image
            $item = array_merge($images[0], $item);
            // Add the other images as array
            $item['images'] = $images;
        }

        return $item;
    }

    /**
     * Get the number of items in a category.
     *
     * @param int $categoryId
     *
     * @return int
     */
    public static function getCategoryCount($categoryId)
    {
        return (int) FrontendModel::getContainer()->get('database')->getVar('SELECT COUNT(i.id) AS count
             FROM commerce_products AS i
             WHERE i.category_id = ?', [(int) $categoryId]);
    }

    /**
     * Get all images for a product.
     *
     * @param int $id
     *
     * @return array
     */
    public static function getImages($id)
    {
        $settings = FrontendModel::get('fork.settings')->getForModule('Commerce');

        $items = (array) FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*
             FROM commerce_images AS i
             WHERE i.product_id = ?
             ORDER BY i.sequence', [(int) $id]);

        // init var
        $link = FrontendNavigation::getURLForBlock('Commerce', 'Category');

        // build the item urls
        foreach ($items as &$item) {
            $basePath = FRONTEND_FILES_URL . '/Commerce/' . $item['product_id'];
            $item['image'] = $basePath . '/source/' . $item['filename'];
            $item['image_icon'] = $basePath . '/64x64/' . $item['filename'];
            $item['image_thumb'] = $basePath . '/128x128/' . $item['filename'];
            $item['image_dim1'] = $basePath . '/' . $settings['width1'] . 'x' . $settings['height1'] . '/' . $item['filename'];
            $item['image_dim2'] = $basePath . '/' . $settings['width2'] . 'x' . $settings['height2'] . '/' . $item['filename'];
            $item['image_dim3'] = $basePath . '/' . $settings['width3'] . 'x' . $settings['height3'] . '/' . $item['filename'];
        }

        return $items;
    }

    /**
     * Update an order.
     *
     * @param array $item
     * @param int   $orderId
     *
     * @return int
     */
    public static function updateOrder($item, $orderId)
    {
        // set date
        $item['date'] = FrontendModel::getUTCDate();

        // get db
        $db = FrontendModel::getContainer()->get('database');

        // update
        $db->update('commerce_orders', $item, 'id = ?', [(int) $orderId]);
    }

    /**
     * Parse the search results for this module.
     *
     * Note: a module's search function should always:
     *        - accept an array of entry id's
     *        - return only the entries that are allowed to be displayed, with their array's index being the entry's id
     *
     * @param array $ids the ids of the found results
     *
     * @return array
     */
    public static function search(array $ids): array
    {
        $ids = array_map('intval', array_values($ids));
        $repository = FrontendModel::getContainer()->get('commerce.repository.product');

        return $repository->search($ids);
    }

    /**
     * Fetches a certain category.
     *
     * @param string $URL
     *
     * @return array
     */
    public static function getBrandFromUrl($URL)
    {
        $item = (array) FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
             m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
             m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
             m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url
             FROM commerce_brands AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE m.url = ?', [(string) $URL]);

        // no results?
        if (empty($item)) {
            return [];
        }

        // create full url
        $item['full_url'] = FrontendNavigation::getURLForBlock('Commerce', 'Brand') . '/' . $item['url'];

        return $item;
    }

    /**
     * Get all category items (at least a chunk).
     *
     * @param int            $categoryId
     * @param int [optional] $limit      The number of items to get
     * @param int [optional] $offset     The offset
     *
     * @return array
     */
    public static function getAllByBrand($brandId, $limit = 10, $offset = 0)
    {
        $items = (array) FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*, m.url
             FROM commerce_products AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE i.brand_id = ? AND i.language = ?
             ORDER BY i.sequence ASC, i.id DESC LIMIT ?, ?', [$brandId, FRONTEND_LANGUAGE, (int) $offset, (int) $limit]);

        // no results?
        if (empty($items)) {
            return [];
        }

        // get detail action url
        $detailUrl = FrontendNavigation::getURLForBlock('Commerce', 'Detail');

        // prepare items for search
        foreach ($items as &$item) {
            $img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM commerce_images WHERE product_id = ? ORDER BY sequence', [(int) $item['id']]);
            if ($img) {
                $item['image'] = FRONTEND_FILES_URL . '/commerce/' . $item['id'] . '/200x200/' . $img['filename'];
            } else {
                $item['image'] = '/' . APPLICATION . '/modules/commerce/layout/images/dummy.png';
            }

            $item['full_url'] = $detailUrl . '/' . $item['url'];
        }

        // return
        return $items;
    }
}
