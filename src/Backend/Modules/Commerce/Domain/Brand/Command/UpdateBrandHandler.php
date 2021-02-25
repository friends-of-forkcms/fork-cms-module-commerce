<?php

namespace Backend\Modules\Commerce\Domain\Brand\Command;

use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Brand\BrandRepository;

final class UpdateBrandHandler
{
    /** @var BrandRepository */
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function handle(UpdateBrand $updateBrand): void
    {
        $brand = Brand::fromDataTransferObject($updateBrand);
        $this->brandRepository->add($brand);

        $updateBrand->setBrandEntity($brand);
    }
}
