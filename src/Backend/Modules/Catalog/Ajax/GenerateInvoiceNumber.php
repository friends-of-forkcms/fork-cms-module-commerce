<?php

namespace Backend\Modules\Catalog\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Catalog\Domain\Order\Event\OrderGenerateInvoiceNumber;
use Backend\Modules\Catalog\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Catalog\Domain\Order\OrderRepository;
use Symfony\Component\HttpFoundation\Response;

class GenerateInvoiceNumber extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        /**
         * @var OrderRepository $orderRepository
         */
        $orderRepository = $this->get('catalog.repository.order');

        try {
            $order = $orderRepository->findOneById($this->getRequest()->request->getInt('order'));
        } catch (OrderNotFound $e) {
            $this->output(Response::HTTP_NOT_FOUND, null, $e->getMessage());
            return;
        }

        /** @var OrderGenerateInvoiceNumber $orderGenerateInvoiceNumber */
        $orderGenerateInvoiceNumber = $this->get('event_dispatcher')->dispatch(
            OrderGenerateInvoiceNumber::EVENT_NAME,
            new OrderGenerateInvoiceNumber($order)
        );

        // success output
        $this->output(
            Response::HTTP_OK,
            [
                'invoiceNumber' => $orderGenerateInvoiceNumber->getOrder()->getInvoiceNumber(),
                'invoiceDate' => $orderGenerateInvoiceNumber->getOrder()->getInvoiceDate()->format('d-m-Y'),
            ]
        );
    }
}
