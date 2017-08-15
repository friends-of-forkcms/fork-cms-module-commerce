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
use Backend\Modules\Catalog\Domain\Category\CategoryType;
use Backend\Modules\Catalog\Domain\Category\Command\Create;
use Backend\Modules\Catalog\Domain\Category\Event\Created;
use Symfony\Component\Form\Form;

/**
 * This is the add category-action, it will display a form to create a new category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddCategory extends BackendBaseActionAdd
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

        $createCategory = $this->createCategory($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createCategory->getCategoryEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createCategory->title,
                ]
            )
        );
    }

    private function createCategory(Form $form): Create
    {
        $createCategory = $form->getData();

        // The command bus will handle the saving of the category in the database.
        $this->get('command_bus')->handle($createCategory);

        return $createCategory;
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

    private function getForm(): Form
    {
        $form = $this->createForm(
            CategoryType::class,
            new Create(),
            [
                'categories' => $this->get('catalog.repository.category')->getTree(Locale::workingLocale())
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
