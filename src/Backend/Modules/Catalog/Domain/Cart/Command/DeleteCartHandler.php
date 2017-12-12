<?php

namespace Backend\Modules\Catalog\Domain\Cart\Command;

use Backend\Modules\Catalog\Domain\Cart\CartRepository;

final class DeleteCartHandler
{
    /** @var CartRepository */
    private $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function handle(DeleteCart $deleteCart): void
    {
        $this->cartRepository->remove(
            $deleteCart->cart
        );
    }
}
