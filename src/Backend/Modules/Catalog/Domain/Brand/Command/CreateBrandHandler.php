<?php

namespace Backend\Modules\Catalog\Domain\Brand\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Brand\Brand;
use Backend\Modules\Catalog\Domain\Brand\BrandRepository;
use Common\ModuleExtraType;

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
