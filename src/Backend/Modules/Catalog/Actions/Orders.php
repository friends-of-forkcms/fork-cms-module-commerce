<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Order\DataGrid;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;

/**
 * This is the orders-action , it will display the overview of orders
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Orders extends BackendBaseActionIndex
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        /**
         * @var OrderRepository
         */
        $orderRepository = $this->get('catalog.repository.order');

        $this->template->assign('dataGridModeration', DataGrid::getHtml(Locale::workingLocale(), Order::STATUS_MODERATION));
        $this->template->assign('moderationCount', $orderRepository->getStatusCount(Order::STATUS_MODERATION));

        $this->template->assign('dataGridCompleted', DataGrid::getHtml(Locale::workingLocale(), Order::STATUS_COMPLETED));
        $this->template->assign('completedCount', $orderRepository->getStatusCount(Order::STATUS_COMPLETED));

        $this->parse();
        $this->display();
    }
}
