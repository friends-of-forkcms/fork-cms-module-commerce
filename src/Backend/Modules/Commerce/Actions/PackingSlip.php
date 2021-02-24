<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\Action;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;
use Frontend\Core\Engine\TwigTemplate;
use Symfony\Component\HttpFoundation\Response;

class PackingSlip extends Action
{
    private ?Order $order;

    public function execute(): void
    {
        parent::execute();

        $this->order = $this->getOrder();

        $this->generateHTML();
    }

    private function generateHTML(): string
    {
        /** @var TwigTemplate $template */
        $template = $this->get('templating');
        $template->assign('order', $this->order);

        return $template->getContent($this->getModule().'/Layout/Templates/'.$this->getAction().'.html.twig');
    }

    public function getContent(): Response
    {
        $filename = Language::lbl('Order').'-'.$this->order->getId().'.pdf';

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($this->generateHTML()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }

    /**
     * @return Order
     *
     * @throws \Common\Exception\RedirectException
     * @throws \Exception
     */
    private function getOrder(): ?Order
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->get('commerce.repository.order');

        try {
            return $orderRepository->findOneById($this->getRequest()->query->getInt('id'));
        } catch (OrderNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }

        return null;
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
