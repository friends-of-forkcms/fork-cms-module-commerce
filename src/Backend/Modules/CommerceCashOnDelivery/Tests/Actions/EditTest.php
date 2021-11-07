<?php

namespace Backend\Modules\CommerceCashOnDelivery\Tests\Actions;

use Backend\Core\Tests\BackendWebTestCase;
use Backend\Modules\Commerce\Domain\OrderStatus\Factory\OrderStatusFactory;
use Backend\Modules\Extensions\Engine\Model as BackendExtensionsModel;
use Symfony\Bundle\FrameworkBundle\Client;
use Zenstruck\Foundry\Test\Factories;

class EditTest extends BackendWebTestCase
{
    use Factories;

    // Required by Foundry to know when kernel has booted. Can be removed in later Symfony versions.
    protected static bool $booted = false;

    protected function setUp(): void
    {
        parent::setUp();
        static::$booted = true;

        // Install the module(s)
        BackendExtensionsModel::installModule('Commerce');
        BackendExtensionsModel::installModule('CommerceCashOnDelivery');

        // Create a few order statuses to connect to the payment method
        OrderStatusFactory::createMany(3);
    }

    /** @test */
    public function it_can_edit_the_payment_method(Client $client): void
    {
        $this->login($client);

        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/commerce/edit_payment_method?id=1',
            ['form name="cash_on_delivery_payment_method" method="post"']
        );

        // Edit the payment method and submit the form
        $orderStatus = OrderStatusFactory::random();
        $form = $this->getFormForSubmitButton($client, 'Save');
        $client->submit($form, [
            'cash_on_delivery_payment_method[name]' => 'Cash On Delivery (EditTest)',
            'cash_on_delivery_payment_method[orderInitId]' => $orderStatus->getId(),
            'cash_on_delivery_payment_method[isEnabled]' => true,
        ]);
        $client->followRedirect();

        // Receive a 200 OK and be redirected to the index page
        self::assertIs200($client);
        self::assertCurrentUrlContains($client, '/private/en/commerce/payment_methods?report=edited');
        self::assertResponseHasContent($client->getResponse(), 'Cash On Delivery (EditTest)');

        // Open the edit page again and assert that the values were saved correctly
        $client->request('GET', '/private/en/commerce/edit_payment_method?id=1');
        self::assertResponseHasContent($client->getResponse(), $orderStatus->getTitle());
        $formValues = $this->getFormForSubmitButton($client, 'Save')->getValues();
        self::assertEquals('Cash On Delivery (EditTest)', $formValues['cash_on_delivery_payment_method[name]']);
        self::assertEquals((string) $orderStatus->getId(), $formValues['cash_on_delivery_payment_method[orderInitId]']);
        self::assertEquals('1', $formValues['cash_on_delivery_payment_method[isEnabled]']);
    }
}
