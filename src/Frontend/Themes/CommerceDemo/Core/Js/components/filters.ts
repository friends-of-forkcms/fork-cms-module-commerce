// Selectors
const sortDropdownSelector = '.js-product-sort';
const filtersSelector = '.js-product-filters';

/**
 * This file implements basic webshop filtering logic. It parses the checked filter checkboxes and sort dropdown
 * and updates the url accordingly.
 */
export const filters = (): void => {
    // Early return if we do not have filters or sorting on this page.
    const sortElement = document.querySelector<HTMLInputElement>(sortDropdownSelector);
    const filterElements = document.querySelectorAll<HTMLInputElement>(`${filtersSelector} input`);
    if (!sortElement && filterElements.length === 0) {
        return;
    }

    // Reset filters to the checked values based on querystring
    setFiltersOnPageLoad();

    // When we change the sorting or filter values, trigger an update.
    // We use Event Delegation here, so we don't need to bind/unbind event listeners when the DOM changes.
    document.addEventListener('input', (event) => {
        const target = event.target as HTMLInputElement;
        const sortingElement = target.closest(sortDropdownSelector);
        const filterElement = target.closest(`${filtersSelector} input`);

        if (sortingElement || filterElement) {
            onUpdateFilters();
        }
    });
};

/**
 * When the sort/filter input values change, recalculate the map of filters and reload the page.
 */
function onUpdateFilters(): void {
    // Fetch the sort order
    const sort = document.querySelector<HTMLInputElement>(sortDropdownSelector)!.value;

    // Fetch the checked filter values, and store them as selected filters.
    // To do so, we create a Map object with the filter name as key, and an array of selected values.
    const checkedFilterElements = document.querySelectorAll(`${filtersSelector} input:checked`);
    const selectedFilters = Array.from(checkedFilterElements).reduce(
        (result: Map<string, string[]>, filter: Element) => {
            const filterName = filter.getAttribute('data-filter') as string;
            const filterValue = filter.getAttribute('data-filter-value') as string;

            if (!result.has(filterName)) {
                result.set(filterName, []);
            }
            const filterValues = result.get(filterName)!;
            result.set(filterName, [...filterValues, filterValue]);
            return result;
        },
        new Map(),
    );

    updateUrl(selectedFilters, sort);
}

/**
 * Transform the sort and filter values into a URL querystring and reload the page
 */
function updateUrl(filters: Map<string, string[]>, sort: string): void {
    const baseUrl = window.jsData['Commerce']['filterUrl'];
    const urlParams = new URLSearchParams();

    // Add the query parameters for sorting and filters
    urlParams.append('sort', sort);
    Array.from(filters).forEach(([filterName, filterValues]) => urlParams.append(filterName, filterValues.join(',')));

    // Reload the page with filters applied
    setTimeout(() => {
        window.location.href = `${baseUrl}?${urlParams.toString()}`;
    }, 50);
}

/**
 * Update the selected filters based on the request query
 */
function setFiltersOnPageLoad(): void {
    const params = new URLSearchParams(window.location.search);
    params.forEach((filterValues, filterName) => {
        filterValues.split(',').forEach((filterValue) => {
            const filterElement = document.querySelector<HTMLInputElement>(
                `[data-filter="${filterName}"][data-filter-value="${filterValue}"]`,
            );

            if (filterElement) {
                filterElement.checked = true;
            }
        });
    });
}
