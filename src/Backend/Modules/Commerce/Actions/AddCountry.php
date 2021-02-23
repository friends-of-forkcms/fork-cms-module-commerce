<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Domain\Country\CountryType;
use Backend\Modules\Commerce\Domain\Country\Command\CreateCountry;
use Backend\Modules\Commerce\Domain\Country\Event\Created;
use Symfony\Component\Form\Form;

/**
 * This is the add country-action, it will display a form to create a new country
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddCountry extends BackendBaseActionAdd
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();
        if (! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $createCountry = $this->createCountry($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createCountry->getCountryEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var'    => $createCountry->name,
                ]
            )
        );
    }

    private function createCountry(Form $form): CreateCountry
    {
        $createCountry = $form->getData();

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createCountry);

        return $createCountry;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Countries',
            null,
            null,
            $parameters
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            CountryType::class,
            new CreateCountry()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
