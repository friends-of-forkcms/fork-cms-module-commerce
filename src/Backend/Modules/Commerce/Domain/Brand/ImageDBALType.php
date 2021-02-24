<?php

namespace Backend\Modules\Commerce\Domain\Brand;

use Common\Doctrine\Type\AbstractImageType;
use Common\Doctrine\ValueObject\AbstractImage;

final class ImageDBALType extends AbstractImageType
{
    /**
     * @return Image
     */
    protected function createFromString(string $imageFileName): AbstractImage
    {
        return Image::fromString($imageFileName);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'commerce_brand_image_type';
    }
}
