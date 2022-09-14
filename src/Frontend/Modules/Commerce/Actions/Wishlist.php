<?php

namespace Frontend\Modules\Commerce\Actions;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use JsonException;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Wishlist extends FrontendBaseBlock
{
    private const WISHLIST_COOKIE_NAME = 'wishlist-ids';

    /** @var array<int,Product> */
    private array $products = [];

    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->getData();
        $this->parse();
        $this->handlePdfDownload();
    }

    private function getData(): void
    {
        if (!$this->getRequest()->cookies->has(self::WISHLIST_COOKIE_NAME)) {
            return;
        }

        // Retrieve product ids from cookie
        try {
            $serializedCookieValue = $this->getRequest()->cookies->get(self::WISHLIST_COOKIE_NAME, '[]');
            $cookieValue = json_decode($serializedCookieValue, false, 512, JSON_THROW_ON_ERROR);
            $productIds = array_map('intval', $cookieValue);
        } catch (JsonException $e) {
            return;
        }

        // Retrieve products from DB
        /** @var ProductRepository $productRepository */
        $productRepository = $this->get('commerce.repository.product');
        $this->products = $productRepository->findActiveByIds($productIds);
    }

    private function parse(): void
    {
        $this->template->assign('products', $this->products);

        // Price VAT setting
        $this->template->assign('includeVAT', $this->get('fork.settings')->get('Commerce', 'show_prices_with_vat', true));
    }

    public function handlePdfDownload(): void
    {
        $request = $this->getRequest();
        if ($request->isMethod(Request::METHOD_POST) && $request->request->get('action') === 'create-pdf') {
            $this->generatePdfResponse()->send();
        }
    }

    private function generatePdfResponse(): Response
    {
        /** @var Pdf $pdf */
        $pdf = $this->get('knp_snappy.pdf');

        // Generate HTML
        $this->loadTemplate('Commerce/Layout/Templates/WishlistPdfDownload.html.twig', true);
        $html = $this->getContent();

        // Create filename
        $siteTitle = $this->get('fork.settings')->get('Core', 'site_title_' . Locale::frontendLanguage());
        $filename = urlencode($siteTitle) . '_' . Language::lbl('Wishlist') . '.pdf';

        // Create pdf response
        $pdf->setOption('viewport-size', '1280x1024');

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );
    }
}
