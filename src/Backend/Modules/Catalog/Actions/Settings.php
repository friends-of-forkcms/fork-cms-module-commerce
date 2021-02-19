<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Catalog\Domain\Settings\Command\UpdateSettings;
use Backend\Modules\Catalog\Domain\Settings\Event\SettingsUpdated;
use Backend\Modules\Catalog\Domain\Settings\SettingsType;
use Common\Exception\RedirectException;
use Symfony\Component\Form\Form;

/**
 * This is the settings action, it will display a form to set general catalog settings.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Settings extends BackendBaseActionEdit
{
    /**
     * Execute the action
     *
     * @throws RedirectException
     * @throws \Exception
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
     * @param array $parameters
     *
     * @return string
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
