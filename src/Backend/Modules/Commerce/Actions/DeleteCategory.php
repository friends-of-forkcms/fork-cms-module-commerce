<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\Event\Deleted;
use Backend\Modules\Commerce\Domain\Category\Command\DeleteCategory as DeleteCommand;
use Backend\Modules\Commerce\Domain\Category\Exception\CategoryNotFound;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class DeleteCategory extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());
        if (! $deleteForm->isSubmitted() || ! $deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $category = $this->getCategory((int)$deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($category));

            $this->get('event_dispatcher')->dispatch(
                Deleted::EVENT_NAME,
                new Deleted($category)
            );

            $this->redirect($this->getBackLink(['report' => 'deleted', 'var' => $category->getTitle()]));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->redirect($this->getBackLink(['error' => 'products-connected']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Categories',
            null,
            null,
            $parameters
        );
    }

    private function getCategory(int $id): Category
    {
        try {
            return $this->get('commerce.repository.category')->findOneByIdAndLocale(
                $id,
                Locale::workingLocale()
            );
        } catch (CategoryNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
