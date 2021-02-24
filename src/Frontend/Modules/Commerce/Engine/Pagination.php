<?php

namespace Frontend\Modules\Commerce\Engine;

use Frontend\Core\Language\Language;

class Pagination
{
    /**
     * @var int
     */
    private $item_count;

    /**
     * @var int
     */
    private $items_per_page;

    /**
     * @var int
     */
    private $current_page;

    /**
     * @var int
     */
    private $page_count;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var int
     */
    private $page_numbers_to_display = 3;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * Set the item count.
     *
     * @param int $item_count
     */
    public function setItemCount($item_count)
    {
        $this->item_count = (int) $item_count;
    }

    /**
     * Set the items per page.
     *
     * @param int $items_per_page
     */
    public function setItemsPerPage($items_per_page)
    {
        $this->items_per_page = (int) $items_per_page;
    }

    /**
     * Set the current page.
     *
     * @param int $current_page
     */
    public function setCurrentPage($current_page)
    {
        $this->current_page = (int) $current_page;
    }

    /**
     * Set the base url.
     *
     * @param string $base_url
     */
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
    }

    /**
     * Get the page count.
     *
     * @return int
     */
    public function getPageCount()
    {
        if (!$this->page_count) {
            $this->page_count = ceil($this->item_count / $this->items_per_page);

            // At least page 1 should be available
            if ($this->page_count == 0) {
                $this->page_count = 1;
            }
        }

        return $this->page_count;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * Render the pagination in an array.
     *
     * @return array
     */
    public function render()
    {
        return [
            'currentPage' => $this->getCurrentPage(),
            'pageCount' => $this->getPageCount(),
            'pagesLeft' => $this->getPagesLeft(),
            'pagesMiddle' => $this->getPagesMiddle(),
            'pagesRight' => $this->getPagesRight(),
            'leftEllipses' => $this->useLeftEllipses(),
            'rightEllipses' => $this->useRightEllipses(),
            'showPrevious' => $this->showPrevious(),
            'showNext' => $this->showNext(),
            'previousNumber' => $this->getPreviousNumber(),
            'nextNumber' => $this->getNextNumber(),
            'urlPrevious' => $this->getUrlPrevious(),
            'urlNext' => $this->getUrlNext(),
            'baseUrl' => $this->base_url,
        ];
    }

    /**
     * Get the pages on the left side.
     *
     * @return array
     */
    private function getPagesLeft()
    {
        $pages = [];

        // Do nothing when there are no left pages
        if (!$this->hasLeftPages()) {
            return $pages;
        }

        for ($i = 1; $i <= $this->page_numbers_to_display; ++$i) {
            $pages[] = [
                'selected' => ($i == $this->current_page),
                'number' => (int) $i,
                'url' => $this->buildUrl($i),
            ];
        }

        return $pages;
    }

    /**
     * Get the right pages.
     *
     * @return array
     */
    private function getPagesRight()
    {
        $pages = [];
        $right_numbers = $this->getPageCount() - $this->page_numbers_to_display;

        // Do nothing when there are no left pages
        if (!$this->hasRightPages()) {
            return $pages;
        }

        if ($right_numbers > 0) {
            for ($i = ($right_numbers + 1); $i <= $this->getPageCount(); ++$i) {
                $pages[] = [
                    'selected' => ($i == $this->current_page),
                    'number' => (int) $i,
                    'url' => $this->buildUrl($i),
                ];
            }
        }

        return $pages;
    }

    /**
     * Check if there are left pages.
     *
     * @return bool
     */
    private function hasLeftPages()
    {
        return $this->getPageCount() > ($this->page_numbers_to_display * 3);
    }

    /**
     * Check if there are right pages.
     *
     * @return bool
     */
    private function hasRightPages()
    {
        return $this->getPageCount() > ($this->page_numbers_to_display * 3);
    }

    /**
     * Get the pages in the middle.
     *
     * @return array
     */
    private function getPagesMiddle()
    {
        $pages = [];

        if (!$this->hasRightPages() && !$this->hasLeftPages()) {
            for ($i = 1; $i <= $this->getPageCount(); ++$i) {
                $pages[] = [
                    'selected' => ($i == $this->current_page),
                    'number' => $i,
                    'url' => $this->buildUrl($i),
                ];
            }
        } else {
            $numbers_to_add = floor($this->page_numbers_to_display / 2);
            $start = (int) $this->current_page - $numbers_to_add;

            // When the start page is smaller then the left pages show the three next
            if ($start < ($this->page_numbers_to_display * 2) && $this->current_page != ($this->page_numbers_to_display * 2)) {
                $start = (int) $this->page_numbers_to_display + 1;
            }

            $end = $start + ($this->page_numbers_to_display - 1);
            if ($end > ($this->getPageCount() - $this->page_numbers_to_display)) {
                $end = $this->getPageCount() - $this->page_numbers_to_display;
                $start = $this->getPageCount() - ($this->page_numbers_to_display * 2) + 1;
            }

            for ($i = $start; $i <= $end; ++$i) {
                $pages[] = [
                    'selected' => ($i == $this->current_page),
                    'number' => $i,
                    'url' => $this->buildUrl($i),
                ];
            }
        }

        return $pages;
    }

    /**
     * Use the left ellipses.
     *
     * @return bool
     */
    private function useLeftEllipses()
    {
        $show = false;

        if ($this->hasLeftPages()) {
            $show = $this->current_page > (($this->page_numbers_to_display * 2) - 1);
        }

        return $show;
    }

    /**
     * Use the left ellipses.
     *
     * @return bool
     */
    private function useRightEllipses()
    {
        $show = false;

        if ($this->hasRightPages()) {
            // Minimal page to show ellipses for
            $minimal_page = ($this->getPageCount() + 1) - ($this->page_numbers_to_display * 2);

            $start_display_number = $this->current_page - floor($this->page_numbers_to_display / 2);

            $show = $start_display_number < $minimal_page;
        }

        return $show;
    }

    /**
     * Get show the previous page button.
     *
     * @return bool
     */
    public function showPrevious()
    {
        return $this->current_page > 1;
    }

    /**
     * Get show the next page button.
     *
     * @return bool
     */
    public function showNext()
    {
        return $this->current_page < $this->getPageCount();
    }

    /**
     * Get the previous number.
     */
    public function getPreviousNumber(): int
    {
        return $this->current_page - 1;
    }

    /**
     * Get the previous url.
     */
    public function getUrlPrevious(): string
    {
        $number = $this->getPreviousNumber();

        return $this->buildUrl($number < 1 ? 1 : $number);
    }

    /**
     * Get the next number.
     */
    public function getNextNumber(): int
    {
        return $this->current_page + 1;
    }

    /**
     * Get show the next page button.
     */
    public function getUrlNext(): string
    {
        $number = $this->getNextNumber();

        return $this->buildUrl(($number < $this->getPageCount() ? $number : $this->getPageCount()));
    }

    /**
     * Build the url based on the base url.
     *
     * @param int $number
     *
     * @return string
     */
    private function buildUrl($number)
    {
        // Only return the number when there is no base url
        if (!$this->base_url) {
            return false;
        }

        $parameters = array_merge($this->parameters, [Language::lbl('Page') => $number]);

        $parameterString = implode('&', array_map(
            function ($v, $k) {
                return sprintf('%s=%s', $k, $v);
            },
            $parameters,
            array_keys($parameters)
        ));

        return $this->base_url.'?'.$parameterString;
    }

    /**
     * Overwrite the page count.
     */
    public function setPageCount(int $page_count)
    {
        $this->page_count = $page_count;
    }

    /**
     * Add parameter.
     *
     * @var string
     * @var mixed
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Add parameters.
     *
     * @var array
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }
}
