<?php

namespace Backend\Modules\Commerce\Domain\Brand\Command;

use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Brand\BrandRepository;

final class CreateBrandHandler
{
    private BrandRepository $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function handle(CreateBrand $createBrand): void
    {
        $brand = Brand::fromDataTransferObject($createBrand);
        $this->brandRepository->add($brand);

        $createBrand->setBrandEntity($brand);
    }
}
