<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Domain\Settings\Command\UpdateSettings;
use Backend\Modules\Commerce\Domain\Settings\Event\SettingsUpdated;
use Backend\Modules\Commerce\Domain\Settings\SettingsType;
use Symfony\Component\Form\Form;

/**
 * This is the settings action, it will display a form to set general commerce settings.
 */
class Settings extends BackendBaseActionEdit
{
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

        $this->updateSettings($form);

        $this->get('event_dispatcher')->dispatch(
            SettingsUpdated::EVENT_NAME,
            new SettingsUpdated()
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                ]
            )
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            SettingsType::class,
            new UpdateSettings($this->get('fork.settings'))
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateSettings(Form $form): UpdateSettings
    {
        /** @var UpdateSettings $updateSettings */
        $updateSettings = $form->getData();

        // The command bus will handle the saving of the product in the database.
        $this->get('command_bus')->handle($updateSettings);

        return $updateSettings;
    }

    /**
     * @throws \Exception
     */
    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Settings',
            null,
            null,
            $parameters
        );
    }
}
