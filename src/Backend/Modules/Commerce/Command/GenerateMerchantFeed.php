<?php

namespace Backend\Modules\Commerce\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Common\ModulesSettings;
use DOMDocument;
use DOMElement;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMerchantFeed extends Command
{
    protected static $defaultName = 'commerce:generate-merchant-feed';

    private ProductRepository $productRepository;
    private LoggerInterface $logger;
    private ModulesSettings $settings;
    private string $kernelRootDir;
    private DOMDocument $domDocument;

    public function __construct(
        ProductRepository $productRepository,
        LoggerInterface $logger,
        ModulesSettings $settings,
        string $kernelRootDir
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->settings = $settings;
        $this->kernelRootDir = $kernelRootDir;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->domDocument = new DOMDocument('1.0', 'UTF-8');
        $domFeed = $this->domDocument->createElement('feed');
        $domFeed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $domFeed->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $domFeed->appendChild($this->getTitle($this->domDocument));
        $domLink = $this->domDocument->createElement('link');
        $domLink->setAttribute('rel', 'self');
        $domLink->setAttribute('href', SITE_URL);
        $domFeed->appendChild($domLink);
        $domFeed->appendChild($this->domDocument->createElement('updated', date('Y-m-d\TH:i:s\Z')));
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());

        foreach ($this->settings->get('Core', 'active_languages') as $activeLanguage) {
            $locale = Locale::fromString($activeLanguage);
            $products = $this->productRepository->findActiveByLocaleAndWithGoogleTaxonomyId($locale);

            foreach ($products as $product) {
                $item = $this->domDocument->createElement('entry');
                $item->appendChild($this->domDocument->createElement('g:id', $product->getId()));
                $titleElement = $item->appendChild($this->domDocument->createElement('g:title'));
                $titleElement->appendChild($this->domDocument->createCDATASection(trim($product->getTitle())));
                $item->appendChild($this->createCDATASection('g:link', SITE_URL . $product->getUrl()));
                $descriptionElement = $this->domDocument->createElement('g:description');
                $descriptionElement->appendChild($this->domDocument->createCDATASection($this->getText($product)));
                $item->appendChild($descriptionElement);

                $first = true;
                $maxImages = 10;
                $imageCount = 0;
                foreach ($product->getImages()->getConnectedMediaItems() as $image) {
                    if ($first) {
                        $item->appendChild($this->createCDATASection('g:image_link', SITE_URL . $image->getWebPath()));

                        $first = false;

                        continue;
                    }

                    $item->appendChild($this->createCDATASection('g:additional_image_link', SITE_URL . $image->getWebPath()));

                    ++$imageCount;
                    if ($imageCount >= $maxImages) {
                        break;
                    }
                }

                // Only add weight when available
                if ($product->getWeight()) {
                    $item->appendChild($this->createCDATASection('g:shipping_weight', $product->getWeight() . ' kg'));
                }

                $item->appendChild($this->createCDATASection('g:condition', 'new'));
                $item->appendChild($this->domDocument->createElement(
                    'g:availability',
                    $this->getAvailability($product)
                ));
                $item->appendChild($this->domDocument->createElement('g:price', $product->getPrice() . ' EUR'));

                if ($product->hasActiveSpecialPrice()) {
                    $item->appendChild($this->domDocument->createElement(
                        'g:sale_price',
                        $moneyFormatter->format($product->getActivePrice(false)) . ' EUR'
                    ));
                }

                $item->appendChild($this->domDocument->createElement(
                    'g:google_product_category',
                    $product->getCategory()->getGoogleTaxonomyId()
                ));

                $item->appendChild($this->createCDATASection(
                    'g:brand',
                    $product->getBrand()->getTitle()
                ));

                if ($gtin = $this->getGtin($product)) {
                    $item->appendChild($this->createCDATASection(
                        'g:gtin',
                        $gtin
                    ));
                }

                $item->appendChild($this->domDocument->createElement(
                    'g:identifier_exists',
                    $gtin ? 'yes' : 'no'
                ));

                $item->appendChild($this->createCDATASection(
                    'g:mpn',
                    $product->getSku()
                ));

                $domFeed->appendChild($item);
            }
        }

        $this->domDocument->appendChild($domFeed);
        $this->domDocument->save($this->kernelRootDir . '/../shopping_feed.xml');
    }

    private function getTitle(): DOMElement
    {
        $title = $this->settings->get('Core', 'site_title_nl');

        return $this->createCDATASection('title', $title);
    }

    private function getAvailability(Product $product)
    {
        if ($product->inStock()) {
            return 'in stock';
        }

        return 'out of stock';
    }

    private function getGtin(Product $product): ?string
    {
        if ($product->getEan13()) {
            return $product->getEan13();
        }

        if ($product->getIsbn()) {
            return $product->getIsbn();
        }

        return null;
    }

    private function getText(Product $product, $html = false): string
    {
        $text = $product->getText();
        $text = preg_replace('/\n/', '', $text);
        $text = preg_replace('/\r/', '', $text);

        if (!$html) {
            $text = strip_tags($text);
        }

        return $text;
    }

    /**
     * @param $value
     */
    private function createCDATASection(string $name, $value): DOMElement
    {
        $domElement = $this->domDocument->createElement($name);

        $domElement->appendChild($this->domDocument->createCDATASection($value));

        return $domElement;
    }
}
