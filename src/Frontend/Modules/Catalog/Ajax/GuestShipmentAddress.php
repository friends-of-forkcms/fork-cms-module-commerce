<?php

namespace Frontend\Modules\Catalog\Ajax;

use Backend\Modules\Catalog\Domain\Account\AccountShipmentAddressType;
use Backend\Modules\Catalog\Domain\Account\Command\CreateShipmentAddress;
use Common\Core\Model;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\TwigTemplate;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GuestShipmentAddress extends FrontendBaseAJAXAction
{
    /**
     * @var bool
     */
    private $hasErrors = false;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * TwigTemplate instance
     *
     * @var TwigTemplate
     */
    protected $template;

    /**
     * @var string
     */
    private $nextStep;

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        parent::execute();

        $this->template = $this->getContainer()->get('templating');
        $this->session = Model::getSession();

        $this->parseShipmentAddress();
        $html = $this->template->getContent('Catalog/Layout/Templates/Checkout/GuestShipmentAddress.html.twig');

        $this->output(
            Response::HTTP_OK,
            [
                'html'      => $html,
                'hasErrors' => $this->hasErrors,
                'nextStep'  => $this->nextStep
            ]
        );
    }

    /**
     * Get the guest form. Returns the path of the template
     *
     * @return bool
     */
    private function parseShipmentAddress(): bool
    {
        $form = $this->getForm();

        // Check if there are any errors in our submit
        if($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->hasErrors = true;
            }

            // Store data in session
            $this->session->set('guest_shipment_address', $form->getNormData());
        }

        // Invalid form
        $this->template->assign('form', $form->createView());

        // Choose our next step
        $this->nextStep = 'shipmentAddress';

        return true;
    }

    private function getForm(): Form
    {
        // Create new form or restore session
        if (!$formData = $this->session->get('guest_shipment_address')) {
            $formData = new CreateShipmentAddress();
        }

        // Load our form
        $form = $this->createForm(
            AccountShipmentAddressType::class,
            $formData
        );

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type FQCN of the form type class i.e: MyClass::class
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return Form
     */
    private function createForm(string $type, $data = null, array $options = []): Form
    {
        return $this->get('form.factory')->create($type, $data, $options);
    }
}
