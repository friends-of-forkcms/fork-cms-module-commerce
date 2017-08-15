<?php

namespace Backend\Modules\Catalog\Domain\Brand\Command;

use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Brand\BrandRepository;

final class UpdateHandler
{
    /** @var BrandRepository */
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function handle(Update $updateBrand): void
    {
        $brand = Brand::fromDataTransferObject($updateBrand);
        $this->brandRepository->add($brand);

        $updateBrand->setBrandEntity($brand);
    }
}
