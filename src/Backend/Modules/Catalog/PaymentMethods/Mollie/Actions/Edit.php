<?php

namespace Backend\Modules\Catalog\PaymentMethods\Mollie\Actions;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\PaymentMethods\Base\Edit as BaseEdit;
use Backend\Modules\Catalog\PaymentMethods\Mollie\MollieDataTransferObject;
use Backend\Modules\Catalog\PaymentMethods\Mollie\MollieType;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Form\Form;

class Edit extends BaseEdit
{
    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            return;
        }

        // Update our data
        $this->updateData($form);

        $this->redirect(
            Model::createUrlForAction(
                'PaymentMethods',
                null,
                null,
                [
                    'report'    => 'edited',
                    'var'       => '',
                    'highlight' => $this->getDataGridRowKey(),
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function install(): void
    {
        parent::install();

        $query = $this->entityManager->getConnection()->prepare(
            'CREATE TABLE IF NOT EXISTS `catalog_orders_mollie_payments` (
                `order_id` int(11) unsigned NOT NULL,
                `method` VARCHAR(150) NOT NULL DEFAULT \'idl\',
                `transaction_id` varchar(32) NOT NULL,
                `bank_account` varchar(15) NOT NULL,
                `bank_status` varchar(20) NOT NULL,
                PRIMARY KEY (`order_id`),
                UNIQUE KEY `transaction_id` (`transaction_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    protected function uninstall(): void
    {
        parent::uninstall();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count', 'count');

        $query = $this->entityManager->createNativeQuery(
            'SELECT COUNT(*) AS `count` FROM `catalog_orders_mollie_payments`',
            $rsm
        );

        $result = $query->getResult();

        if ((int)$result[0]['count'] === 0) {
            $this->entityManager->getConnection()
                                ->prepare('DROP TABLE IF EXISTS `catalog_orders_mollie_payments`')
                                ->execute();
        }
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            MollieType::class,
            $this->getData(new MollieDataTransferObject()),
            [
                'entityManager' => $this->entityManager
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateData(Form $form): void
    {
        // Get the form data
        $data = $form->getData();

        // Save the form data
        $this->setData($form->getData(), true);

        // Install our payment method or not
        $this->installPaymentMethod($data->installed);
    }
}
