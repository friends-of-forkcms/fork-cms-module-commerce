<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\StockStatus\Exception\StockStatusNotFound;
use Backend\Modules\Catalog\Domain\StockStatus\Exception\StockStatusValueNotFound;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatusType;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatusRepository;
use Backend\Modules\Catalog\Domain\StockStatus\Command\Update;
use Backend\Modules\Catalog\Domain\StockStatus\Event\Updated;
use Symfony\Component\Form\Form;

/**
 * This is the edit stock-status-action, it will display a form to edit a stock status
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditStockStatus extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $stockStatus = $this->getStockStatus();

        $form = $this->getForm($stockStatus);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $stockStatus->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteStockStatus'
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('stockStatus', $stockStatus);

            $this->parse();
            $this->display();

            return;
        }

        /** @var Update $updateStockStatus */
        $updateStockStatus = $this->updateStockStatus($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateStockStatus->getStockStatusEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report'    => 'edited',
                    'var'       => $updateStockStatus->title,
                    'highlight' => 'row-' . $updateStockStatus->getStockStatusEntity()->getId(),
                ]
            )
        );
    }

    private function getStockStatus(): StockStatus
    {
        /** @var StockStatusRepository stockStatusRepository */
        $stockStatusRepository = $this->get('catalog.repository.stock_status');

        try {
            return $stockStatusRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (StockStatusNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'StockStatuses',
            null,
            null,
            $parameters
        );
    }

    private function getForm(StockStatus $stockStatus): Form
    {
        $form = $this->createForm(
            StockStatusType::class,
            new Update($stockStatus)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateStockStatus(Form $form): Update
    {
        /** @var Update $updateStockStatus */
        $updateStockStatus = $form->getData();

        // The command bus will handle the saving of the stock status in the database.
        $this->get('command_bus')->handle($updateStockStatus);

        return $updateStockStatus;
    }
}
