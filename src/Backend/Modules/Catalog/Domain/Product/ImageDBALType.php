<?php

namespace Backend\Modules\Catalog\Domain\Product;

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
		return 'catalog_product_image_type';
	}
}