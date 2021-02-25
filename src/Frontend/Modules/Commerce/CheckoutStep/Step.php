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
    /**
     * @var string
     */
    public static $stepIdentifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $active = false;

    /**
     * @var boolean
     */
    protected $current = false;

    /**
     * @var boolean
     */
    protected $complete = false;

    /**
     * @var boolean
     */
    protected $reachable = false;

    /**
     * @var bool
     */
    protected $showBreadcrumbs = true;

    /**
     * @var Step
     */
    protected $previousStep;

    /**
     * @var Step
     */
    protected $nextStep;

    /**
     * @var TwigTemplate
     */
    protected $template;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var CheckoutProgress
     */
    protected $checkoutProgress;

    /**
     * @var CommerceAccount
     */
    protected $account;

    /**
     * @var array
     */
    protected $jsFiles = [];

    /**
     * Step constructor.
     */
    public function __construct()
    {
        $this->template = Model::get('templating');
        $this->session = \Common\Core\Model::getSession();
        $this->cart = $this->getCartRepository()->getActiveCart(false);

        $this->init();
    }

    public function init()
    {

    }

    /**
     * @param string $name
     */
    public function setStepName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStepName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isReachable(): bool
    {
        if ($this->previousStep) {
            $this->reachable = $this->previousStep->isComplete();
        }

        return $this->reachable;
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->complete;
    }

    /**
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->current;
    }

    /**
     * @param bool $current
     */
    public function setCurrent(bool $current): void
    {
        $this->current = $current;
    }

    public function invalidateStep()
    {
        if ($this->nextStep) {
            $this->nextStep->invalidateStep();
        }
    }

    protected function goToStep(string $class)
    {
        die();
    }

    public function render()
    {
        $name = class_basename($this);
        $flashErrors = [];

        if ($this->session->has('flash_errors')) {
            $flashErrors = $this->session->get('flash_errors', []);
            $this->session->remove('flash_errors');
        }

        $this->template->assign('flashErrors', $flashErrors);
        $this->template->assign('cart', $this->cart);
        $this->template->assign('step', $this);

        $html = $this->template->getContent('Commerce/Layout/Templates/Checkout/Step/' . $name . '.html.twig');

        return $html;
    }

    protected function get($serviceId)
    {
        return Model::get($serviceId);
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
    public function createForm(string $type, $data = null, array $options = []): Form
    {
        return $this->get('form.factory')->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance.
     *
     * @param mixed $data The initial data for the form
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
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return Model::getRequest();
    }

    /**
     * Get the account repository
     *
     * @return AccountRepository
     */
    protected function getAccountRepository(): AccountRepository
    {
        return $this->get('commerce.repository.account');
    }

    /**
     * Get the cart repository
     *
     * @return CartRepository
     */
    protected function getCartRepository(): CartRepository
    {
        return $this->get('commerce.repository.cart');
    }

    public function getIdentifier()
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

    public function showBreadcrumbs(): bool
    {
        return $this->showBreadcrumbs;
    }

    /**
     * Add an error which will be displayed once
     *
     * @param string $message
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
        if (Authentication::isLoggedIn() && !$this->account) {
            $this->account = $this->getAccountRepository()->findOneByProfile(Authentication::getProfile());
        }

        return $this->account;
    }

    /**
     * @throws ChangeStepException
     */
    public abstract function execute(): void;
}
