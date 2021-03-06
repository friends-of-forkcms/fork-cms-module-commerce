<?php

namespace Backend\Modules\Commerce\Domain\Cart\Command;

use Backend\Modules\Commerce\Domain\Cart\CartRepository;

final class DeleteCartHandler
{
    private CartRepository $cartRepository;

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
