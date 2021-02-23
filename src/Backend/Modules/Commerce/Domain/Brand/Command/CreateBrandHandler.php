<?php

namespace Backend\Modules\Commerce\Domain\Brand\Command;

use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Brand\BrandRepository;

final class CreateBrandHandler
{
    /** @var BrandRepository */
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function handle(CreateBrand $createBrand): void
    {
        $createBrand->sequence = $this->brandRepository->getNextSequence(
            $createBrand->locale
        );

        $brand = Brand::fromDataTransferObject($createBrand);
        $this->brandRepository->add($brand);

        $createBrand->setBrandEntity($brand);
    }
}
