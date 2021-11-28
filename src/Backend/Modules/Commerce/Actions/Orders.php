<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Domain\Order\DataGrid;
use Backend\Modules\Commerce\Domain\Order\OrderFilterType;
use Backend\Modules\Commerce\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusRepository;
use DateTime;

/**
 * This is the orders-action, it will display the overview of orders.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Orders extends BackendBaseActionIndex
{
    private const PARAM_SEARCH = 'q';
    private const PARAM_ORDER_STATUS = 'order_status';
    private const PARAM_ORDER_DATE_STARTED_AT = 'order_date_started_at';
    private const PARAM_ORDER_DATE_ENDED_AT = 'order_date_ended_at';

    private ?string $searchQuery = null;
    private ?OrderStatus $orderStatus = null;
    private ?string $orderDateStartedAt = null;
    private ?string $orderDateEndedAt = null;

    public function execute(): void
    {
        parent::execute();

        // Filters
        $this->searchQuery = $this->getRequest()->query->get(self::PARAM_SEARCH);
        $this->orderDateStartedAt = $this->getRequest()->query->get(self::PARAM_ORDER_DATE_STARTED_AT);
        $this->orderDateEndedAt = $this->getRequest()->query->get(self::PARAM_ORDER_DATE_ENDED_AT);
        $orderStatusId = $this->getRequest()->query->getInt('order_status');
        if (!empty($orderStatusId)) {
            $this->orderStatus = $this->findOrderStatusById($orderStatusId);
        }

        $this->template->assign(
            'dataGrid',
            DataGrid::getHtml(
                $this->searchQuery,
                $this->orderStatus,
                $this->orderDateStartedAt ? DateTime::createFromFormat('d-m-Y', $this->orderDateStartedAt) : null,
                $this->orderDateEndedAt ? DateTime::createFromFormat('d-m-Y', $this->orderDateEndedAt) : null,
            )
        );

        $this->loadFilterForm();
        $this->parse();
        $this->display();
    }

    private function loadFilterForm(): void
    {
        $filterForm = $this->createForm(
            OrderFilterType::class,
            [
                'search_query' => $this->searchQuery,
                'order_status' => $this->orderStatus,
                'order_date_range' => [
                    $this->orderDateStartedAt,
                    $this->orderDateEndedAt,
                ],
            ],
            [
                'order_statuses' => $this->findAllOrderStatuses(),
            ]
        );

        $filterForm->handleRequest($this->getRequest());

        // check if the form is submitted and then return with a get
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();

            // build the url parameters when required
            $parameters = [];
            if ($data['search_query']) {
                $parameters[self::PARAM_SEARCH] = $data['search_query'];
            }
            if ($data['order_status']) {
                $parameters[self::PARAM_ORDER_STATUS] = $data['order_status']->getId();
            }
            if (is_array($data['order_date_range'])) {
                [$startedAt, $endedAt] = $data['order_date_range'];
                $parameters[self::PARAM_ORDER_DATE_STARTED_AT] = $startedAt->format('d-m-Y');
                $parameters[self::PARAM_ORDER_DATE_ENDED_AT] = $endedAt->format('d-m-Y');
            }

            // redirect to a filtered page
            $this->redirect($this->getBackLink($parameters));
        }

        $this->template->assign('form', $filterForm->createView());
    }

    private function findOrderStatusById(int $orderStatusId): OrderStatus
    {
        /** @var OrderStatusRepository $repository */
        $repository = $this->get('commerce.repository.order_status');

        try {
            return $repository->findOneById($orderStatusId);
        } catch (OrderStatusNotFound $e) {
            $this->redirect($this->getBackLink());
        }
    }

    /**
     * @return array<int, OrderStatus>
     */
    private function findAllOrderStatuses(): array
    {
        /** @var OrderStatusRepository $repository */
        $repository = $this->get('commerce.repository.order_status');

        return $repository->findAll();
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Orders', null, null, $parameters);
    }
}
