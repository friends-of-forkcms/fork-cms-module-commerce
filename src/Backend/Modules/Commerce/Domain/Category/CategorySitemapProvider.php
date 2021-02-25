<?php

namespace Backend\Modules\Commerce\Domain\Category;

use Backend\Core\Language\Locale;
use Common\ModulesSettings;
use JeroenDesloovere\SitemapBundle\Item\ChangeFrequency;
use JeroenDesloovere\SitemapBundle\Provider\SitemapProvider;
use JeroenDesloovere\SitemapBundle\Provider\SitemapProviderInterface;

class CategorySitemapProvider extends SitemapProvider implements SitemapProviderInterface
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ModulesSettings
     */
    private $settings;

    public function __construct(CategoryRepository $categoryRepository, ModulesSettings $settings)
    {
        $this->categoryRepository = $categoryRepository;
        $this->settings = $settings;

        parent::__construct('CommerceCategories');
    }

    public function createItems(): void
    {
        foreach ($this->settings->get('Core', 'active_languages') as $activeLanguage) {
            $locale = Locale::fromString($activeLanguage);
            $categories = $this->categoryRepository->findParents($locale);

            foreach ($categories as $category) {
                $this->addItem($category);
            }
        }
    }

    private function addItem(Category $category)
    {
        $this->createItem($category->getUrl(), $category->getEditedOn(), ChangeFrequency::monthly());

        foreach ($category->getChildren() as $subCategory) {
            $this->addItem($subCategory);
        }
    }
}
