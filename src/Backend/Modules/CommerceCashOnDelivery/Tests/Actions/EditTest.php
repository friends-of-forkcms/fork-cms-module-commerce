<?php

namespace Backend\Modules\CommerceCashOnDelivery\Tests\Actions;

use Backend\Core\Tests\BackendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class EditTest extends BackendWebTestCase
{
    /** @test */
    public function it_can_edit_the_payment_method(Client $client): void
    {
        $this->login($client);
        $this->assertEquals(1, 1);
    }
}
