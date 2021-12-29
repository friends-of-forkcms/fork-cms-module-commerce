<?php

namespace Backend\Modules\Commerce\Engine;

use Backend\Core\Engine\Model as BackendModel;
use SpoonFilter;

/**
 * In this file we store all generic functions that we will be using in the Commerce module.
 */
class Model
{
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
     * Get all data for a given id.
     *
     * @param int $id The Id of the order to fetch?
     *
     * @return array
     */
    public static function getOrder($id)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS ordered_on,
             p.amount AS amount_of_product, c.title AS product_title
             FROM commerce_orders AS i
             INNER JOIN commerce_orders_values AS p ON i.id = p.order_id
             INNER JOIN commerce_products AS c ON p.product_id = c.id
             WHERE i.id = ?
             LIMIT 1', [(int) $id]);
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
}
