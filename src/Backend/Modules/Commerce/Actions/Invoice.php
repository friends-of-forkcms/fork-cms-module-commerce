<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\Action;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\Order\Event\OrderGenerateInvoiceNumber;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;
use Frontend\Core\Engine\TwigTemplate;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

class Invoice extends Action
{
    private Order $order;

    public function execute(): void
    {
        parent::execute();
        $this->order = $this->getOrder();

        // Generate invoice number when it doesn't exists
        if (!$this->order->getInvoiceNumber()) {
            /** @var OrderGenerateInvoiceNumber $generateInvoiceNumber */
            $generateInvoiceNumber = $this->get('event_dispatcher')->dispatch(
                OrderGenerateInvoiceNumber::EVENT_NAME,
                new OrderGenerateInvoiceNumber($this->order)
            );

            $this->order = $generateInvoiceNumber->getOrder();
        }

        $this->generateHTML();
    }

    private function generateHTML(): string
    {
        /** @var TwigTemplate $template */
        $template = $this->get('templating');
        $template->assign('order', $this->order);

        return $template->getContent($this->getModule() . '/Layout/Templates/' . $this->getAction() . '.html.twig');
    }

    public function getContent(): Response
    {
        $filename = Language::lbl('Invoice') . '-' . $this->order->getInvoiceNumber() . '.pdf';

        /** @var Pdf $pdf */
        $pdf = $this->get('knp_snappy.pdf');
        $pdf->setOption('viewport-size', '1280x1024');

        return new Response(
            $pdf->getOutputFromHtml($this->generateHTML()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }

    private function getOrder(): Order
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->get('commerce.repository.order');

        try {
            return $orderRepository->findOneById($this->getRequest()->query->getInt('id'));
        } catch (OrderNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    /**
     * @throws \Exception
     */
    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Orders',
            null,
            null,
            $parameters
        );
    }
}
