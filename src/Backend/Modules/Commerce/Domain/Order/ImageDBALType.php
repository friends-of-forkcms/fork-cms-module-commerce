<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Common\Doctrine\Type\AbstractImageType;
use Common\Doctrine\ValueObject\AbstractImage;

final class ImageDBALType extends AbstractImageType
{
    /**
     * @param string $imageFileName
     *
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
        return 'commerce_order_image_type';
    }
}
