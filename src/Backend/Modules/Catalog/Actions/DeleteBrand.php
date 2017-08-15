<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Brand\Event\Deleted;
use Backend\Modules\Catalog\Domain\Brand\Command\Delete as DeleteCommand;
use Backend\Modules\Catalog\Domain\Brand\Exception\BrandNotFound;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a brand
 *
 * @author Waldo Cosman <waldo@comsa.be>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class DeleteBrand extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());
        if ( ! $deleteForm->isSubmitted() || ! $deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $brand = $this->getBrand((int)$deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($brand));

            $this->get('event_dispatcher')->dispatch(
                Deleted::EVENT_NAME,
                new Deleted($brand)
            );

            $this->redirect($this->getBackLink(['report' => 'deleted', 'var' => $brand->getTitle()]));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->redirect($this->getBackLink(['error' => 'products-connected']));
        }
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

    private function getBrand(int $id): Brand
    {
        try {
            return $this->get('catalog.repository.brand')->findOneByIdAndLocale(
                $id,
                Locale::workingLocale()
            );
        } catch (BrandNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
