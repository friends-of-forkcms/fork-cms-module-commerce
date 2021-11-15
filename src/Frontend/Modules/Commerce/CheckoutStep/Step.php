<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Account\Account as CommerceAccount;
use Backend\Modules\Commerce\Domain\Account\AccountRepository;
use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\CartRepository;
use Frontend\Core\Engine\Model;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Engine\TwigTemplate;
use Frontend\Core\Language\Language;
use Frontend\Modules\Commerce\CheckoutProgress;
use Frontend\Modules\Profiles\Engine\Authentication;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class Step
{
    public static string $stepIdentifier;
    protected string $name;
    protected bool $active = false;
    protected bool $current = false;
    protected bool $complete = false;
    protected bool $reachable = false;
    protected bool $showInBreadcrumbs = true;
    protected Step $previousStep;
    protected Step $nextStep;
    protected TwigTemplate $template;
    protected Cart $cart;
    protected SessionInterface $session;
    protected CheckoutProgress $checkoutProgress;
    protected CommerceAccount $account;
    protected array $jsFiles = [];
    protected string $templatePath;

    public function __construct()
    {
        $this->template = Model::get('templating');
        $this->session = \Common\Core\Model::getSession();
        $this->cart = $this->getCartRepository()->getActiveCart(false);
        $this->templatePath = 'Commerce/Layout/Templates/Checkout/Step/' . class_basename($this) . '.html.twig';

        $this->init();
    }

    public function init(): void
    {
    }

    public function setStepName(string $name): void
    {
        $this->name = $name;
    }

    public function getStepName(): string
    {
        return $this->name;
    }

    public function isReachable(): bool
    {
        if (isset($this->previousStep)) {
            $this->reachable = $this->previousStep->isComplete();
        }

        return $this->reachable;
    }

    public function isComplete(): bool
    {
        return $this->complete;
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): void
    {
        $this->current = $current;
    }

    public function invalidateStep(): void
    {
        if (isset($this->nextStep)) {
            $this->nextStep->invalidateStep();
        }
    }

    protected function goToStep(string $class): void
    {
        exit();
    }

    public function render(): string
    {
        $flashErrors = [];
        if ($this->session->has('flash_errors')) {
            $flashErrors = $this->session->get('flash_errors', []);
            $this->session->remove('flash_errors');
        }

        $this->cart->calculateTotals();

        $this->template->assign('flashErrors', $flashErrors);
        $this->template->assign('cart', $this->cart);
        $this->template->assign('step', $this);

        return $this->template->getContent($this->templatePath);
    }

    protected function get($serviceId)
    {
        return Model::get($serviceId);
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    FQCN of the form type class i.e: MyClass::class
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     */
    public function createForm(string $type, $data = null, array $options = []): Form
    {
        return $this->get('form.factory')->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance.
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormBuilderInterface
     *
     * @final since version 3.4
     */
    protected function createFormBuilder($data = null, array $options = [])
    {
        return $this->get('form.factory')->createBuilder(FormType::class, $data, $options);
    }

    /**
     * Get the request from the container.
     */
    public function getRequest(): Request
    {
        return Model::getRequest();
    }

    /**
     * Get the account repository.
     */
    protected function getAccountRepository(): AccountRepository
    {
        return $this->get('commerce.repository.account');
    }

    protected function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }

    public function getIdentifier(): string
    {
        return static::$stepIdentifier;
    }

    /**
     * @throws ChangeStepException
     */
    protected function goToPreviousStep(): void
    {
        throw new ChangeStepException($this->getPreviousStep(), $this);
    }

    /**
     * @throws ChangeStepException
     */
    protected function goToNextStep(): void
    {
        throw new ChangeStepException($this->getNextStep(), $this);
    }

    public function getUrl(): ?string
    {
        return Navigation::getUrlForBlock('Commerce', 'Cart') . '/' . Language::lbl('Checkout');
    }

    public function setCheckoutProgress(CheckoutProgress $checkoutProgress): void
    {
        $this->checkoutProgress = $checkoutProgress;
    }

    public function setPreviousStep(Step $step): void
    {
        $this->previousStep = $step;
    }

    public function getPreviousStep(): ?Step
    {
        return $this->previousStep;
    }

    public function setNextStep(Step $step): void
    {
        $this->nextStep = $step;
    }

    public function getNextStep(): ?Step
    {
        return $this->nextStep;
    }

    public function shouldShowInBreadcrumbs(): bool
    {
        return $this->showInBreadcrumbs;
    }

    /**
     * Add an error which will be displayed once.
     */
    public function flashError(string $message): void
    {
        $errors = $this->session->get('flash_errors', []);
        $errors[] = $message;
        $this->session->set('flash_errors', $errors);
    }

    protected function addJsFile(string $file): void
    {
        $this->jsFiles[] = $file;
    }

    public function getJsFiles(): array
    {
        return $this->jsFiles;
    }

    protected function getAccount(): ?CommerceAccount
    {
        if (!isset($this->account) && Authentication::isLoggedIn()) {
            $this->account = $this->getAccountRepository()->findOneByProfile(Authentication::getProfile());
        }

        return $this->account;
    }

    /**
     * @throws ChangeStepException
     */
    abstract public function execute(): void;
}
