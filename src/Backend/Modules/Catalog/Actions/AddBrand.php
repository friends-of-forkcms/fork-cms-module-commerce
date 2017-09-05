<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */
use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Brand\BrandType;
use Backend\Modules\Catalog\Domain\Brand\Command\CreateBrand;
use Backend\Modules\Catalog\Domain\Brand\Event\Created;
use Symfony\Component\Form\Form;

/**
 * This is the add brand-action, it will display a form to create a new brand
 *
 * @author Waldo Cosman <waldo_cosman@hotmail.com>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddBrand extends BackendBaseActionAdd
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $createBrand = $this->createBrand($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createBrand->getBrandEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createBrand->title,
                ]
            )
        );
    }

    private function createBrand(Form $form): CreateBrand
    {
        $createBrand = $form->getData();

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createBrand);

        return $createBrand;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Brands',
            null,
            null,
            $parameters
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            BrandType::class,
            new CreateBrand()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
