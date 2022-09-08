<?php

namespace Backend\Modules\CommerceDelivery\Tests\Actions;

use Backend\Core\Tests\BackendWebTestCase;
use Backend\Modules\Commerce\Domain\PaymentMethod\Factory\PaymentMethodFactory;
use Backend\Modules\Commerce\Domain\Vat\Factory\VatFactory;
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
        BackendExtensionsModel::installModule('CommerceDelivery');
    }

    /** @test */
    public function it_can_edit_the_shipment_method(Client $client): void
    {
        $this->login($client);

        // Create a few objects to link the shipment method to
        $vat = VatFactory::new()->create();
        PaymentMethodFactory::createMany(3);

        self::assertPageLoadedCorrectly(
            $client,
            '/private/en/commerce/edit_shipment_method?id=1',
            ['form name="pickup_shipment_method" method="post"']
        );

        // Edit the shipment method and submit the form
        $form = $this->getFormForSubmitButton($client, 'Save');
        $client->submit($form, [
            'pickup_shipment_method[name]' => 'Delivery (EditTest)',
            'pickup_shipment_method[price][tbbc_amount]' => 10.00,
            'pickup_shipment_method[vatId]' => (string) $vat->getId(),
            'pickup_shipment_method[isEnabled]' => true,
            'pickup_shipment_method[availablePaymentMethods][0]' => true,
            'pickup_shipment_method[availablePaymentMethods][1]' => true,
            'pickup_shipment_method[availablePaymentMethods][2]' => true,
        ]);
        $client->followRedirect();

        // Receive a 200 OK and be redirected to the index page
        self::assertIs200($client);
        self::assertCurrentUrlContains($client, '/private/en/commerce/shipment_methods?report=edited');
        self::assertResponseHasContent($client->getResponse(), 'Delivery (EditTest)');

        // Open the edit page again and assert that the values were saved correctly
        $client->request('GET', '/private/en/commerce/edit_shipment_method?id=1');
        self::assertResponseHasContent($client->getResponse(), $vat->getTitle());
        $formValues = $this->getFormForSubmitButton($client, 'Save')->getValues();
        self::assertEquals('Delivery (EditTest)', $formValues['pickup_shipment_method[name]']);
        self::assertEquals(10.00, $formValues['pickup_shipment_method[price][tbbc_amount]']);
        self::assertEquals('1', $formValues['pickup_shipment_method[isEnabled]']);
    }
}
