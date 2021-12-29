<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\Country\Command\UpdateCountry;
use Backend\Modules\Commerce\Domain\Country\Country;
use Backend\Modules\Commerce\Domain\Country\CountryRepository;
use Backend\Modules\Commerce\Domain\Country\CountryType;
use Backend\Modules\Commerce\Domain\Country\Event\Updated;
use Backend\Modules\Commerce\Domain\Country\Exception\CountryNotFound;
use Symfony\Component\Form\Form;

/**
 * This is the edit country-action, it will display a form to edit a country.
 */
class EditCountry extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $country = $this->getCountry();

        $form = $this->getForm($country);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $country->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteCountry',
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('country', $country);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateCountry $updateCountry */
        $updateCountry = $this->updateCountry($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateCountry->getCountryEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                    'var' => $updateCountry->title,
                    'highlight' => 'row-' . $updateCountry->getCountryEntity()->getId(),
                ]
            )
        );
    }

    private function getCountry(): Country
    {
        /** @var CountryRepository $countryRepository */
        $countryRepository = $this->get('commerce.repository.country');

        try {
            return $countryRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (CountryNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
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

    private function getForm(Country $country): Form
    {
        $form = $this->createForm(
            CountryType::class,
            new UpdateCountry($country)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateCountry(Form $form): UpdateCountry
    {
        /** @var UpdateCountry $updateCountry */
        $updateCountry = $form->getData();

        // The command bus will handle the saving of the country in the database.
        $this->get('command_bus')->handle($updateCountry);

        return $updateCountry;
    }
}
