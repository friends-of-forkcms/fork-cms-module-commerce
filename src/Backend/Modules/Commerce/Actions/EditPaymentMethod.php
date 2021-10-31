<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Brand\BrandRepository;
use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodRepository;
use Exception;

/**
 * Action to edit a payment method
 * This will proxy through to the underlying payment module's edit action and template!
 */
class EditPaymentMethod extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $paymentMethod = $this->getPaymentMethod();

        // Load class from the external module
        $className = $this->getPaymentMethodAction($paymentMethod->getModule());
        if (!class_exists($className)) {
            throw new Exception("Class $className is not found!");
        }

        // Load our class and pass the rendered template as output content
        /**
         * @var \Backend\Modules\Commerce\PaymentMethods\Base\Edit $paymentMethodEdit
         */
        $paymentMethodEdit = new $className($this->getKernel());
        $paymentMethodEdit->execute();
        $paymentMethodEdit->display();
        $this->content = $paymentMethodEdit->getContent()->getContent();
    }

    private function getPaymentMethodAction(string $moduleName): string
    {
        return "\\Backend\\Modules\\$moduleName\\Actions\\Edit";
    }

    protected function getPaymentMethod(): PaymentMethod
    {
        /** @var PaymentMethodRepository $paymentMethodRepository */
        $paymentMethodRepository = $this->get('commerce.repository.payment_method');

        try {
            return $paymentMethodRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (PaymentMethodNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('PaymentMethods', null, null, $parameters);
    }
}
