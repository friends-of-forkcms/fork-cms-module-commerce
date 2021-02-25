<?php

namespace Backend\Modules\Commerce\PaymentMethods\Mollie\Actions;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\PaymentMethods\Base\Edit as BaseEdit;
use Backend\Modules\Commerce\PaymentMethods\Mollie\MollieDataTransferObject;
use Backend\Modules\Commerce\PaymentMethods\Mollie\MollieType;
use Doctrine\ORM\Query\ResultSetMapping;
use Mollie\Api\MollieApiClient;
use Symfony\Component\Form\Form;

class Edit extends BaseEdit
{
    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        parent::execute();

        $data = $this->getData(new MollieDataTransferObject());

        $enabledMethods = [];
        if ($data->apiKey) {
            $mollie = new MollieApiClient();
            $mollie->setApiKey($data->apiKey);

            foreach ($mollie->methods->allActive() as $method) {
                $enabledMethods[] = [
                    'id' => $method->id,
                    'description' => $method->description,
                ];
            }
        }

        $form = $this->getForm($data, $enabledMethods);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('enabledMethods', $enabledMethods);
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
                    'report' => 'edited',
                    'var' => '',
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
            'CREATE TABLE IF NOT EXISTS `commerce_orders_mollie_payments` (
                `order_id` int(11) unsigned NOT NULL,
                `method` VARCHAR(150) NOT NULL DEFAULT \'ideal\',
                `transaction_id` varchar(32) NOT NULL,
                `bank_account` varchar(15) DEFAULT NULL,
                `bank_status` varchar(20) DEFAULT NULL,
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
            'SELECT COUNT(*) AS `count` FROM `commerce_orders_mollie_payments`',
            $rsm
        );

        $result = $query->getResult();

        if ((int)$result[0]['count'] === 0) {
            $this->entityManager->getConnection()
                ->prepare('DROP TABLE IF EXISTS `commerce_orders_mollie_payments`')
                ->execute();
        }
    }

    private function getForm(MollieDataTransferObject $data, $enabledMethods = []): Form
    {
        $form = $this->createForm(
            MollieType::class,
            $data,
            [
                'entityManager' => $this->entityManager,
                'enabledMethods' => $enabledMethods,
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
