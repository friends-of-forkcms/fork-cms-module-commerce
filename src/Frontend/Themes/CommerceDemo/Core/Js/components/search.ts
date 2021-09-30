/** @jsx html */
import { html } from 'htm/preact';
import { autocomplete, VNode } from '@algolia/autocomplete-js';
import { requestAjax } from '../api/ForkAPI';
import { memoize, ucfirst } from "../utilities/utils";
import '@algolia/autocomplete-theme-classic/dist/theme.css';
import { lbl, msg } from "./locale";

type ForkSearchItem = {
    id: string;
    module: string;
    url: string;
    full_url: string;
    text: string;
    title: string;
    type?: string;
};

type ForkProductItem = ForkSearchItem & {
    price?: number;
    preview_image_url?: string;
};

type AutocompleteItem = ForkSearchItem | ForkProductItem;

/**
 * This file adds Algolia Autocomplete support to any search button, using the
 * Fork CMS search API.
 */
export async function search(): Promise<void> {
    if (document.querySelector('.aa-Autocomplete')) {
        return;
    }

    // Init the abortcontroller (cancel previous requests)
    let abortController = new AbortController();

    // Fetch locale strings
    const lblProducts = ucfirst(lbl('Products'));
    const msgNoResults = ucfirst(msg('NoSearchResults'));
    const msgSearchPlaceholder = ucfirst(msg('SearchPlaceholder'));

    const { setIsOpen } = autocomplete<AutocompleteItem>({
        container: '.js-search-button',
        placeholder: msgSearchPlaceholder,
        openOnFocus: true,
        detachedMediaQuery: '', // Use detached mode on all breakpoints, it shows a modal instead
        classNames: {
            detachedSearchButton: '!bg-transparent !border-none',
            detachedSearchButtonPlaceholder: 'sr-only',
            detachedSearchButtonIcon: '!cursor-pointer',
        },
        getSources() {
            return [
                {
                    sourceId: 'pages',
                    async getItems({ query }) {
                        if (query === '') {
                            return [];
                        }

                        // Fetch search results from Fork CMS. Filter out the non-products.
                        const response = await loadResults(query);
                        return (response.data || [])
                            .filter((item: AutocompleteItem) => !isProductModule(item.module))
                            .map((item: AutocompleteItem) => {
                                item['type'] = query;
                                return item;
                            });
                    },
                    templates: {
                        noResults() {
                            return msgNoResults;
                        },
                        item({ item }) {
                            return renderPageResult(item);
                        },
                    },
                },
                {
                    sourceId: 'products',
                    async getItems({ query }) {
                        if (query === '') {
                            return [];
                        }

                        // Fetch search results from Fork CMS. Filter out only the products.
                        const response = await loadResults(query);
                        return (response.data || [])
                            .filter((item: AutocompleteItem) => isProductModule(item.module))
                            .map((item: AutocompleteItem) => {
                                item['type'] = query;
                                return item;
                            });
                    },
                    getItemUrl({ item }) {
                        return item.full_url;
                    },
                    templates: {
                        header({ items }) {
                            if (items.length === 0) {
                                return '';
                            }

                            return html`
                                <div>
                                    <span className="aa-SourceHeaderTitle">${lblProducts}</span>
                                    <div className="aa-SourceHeaderLine" />
                                </div>
                            `;
                        },
                        item({ item }) {
                            return renderProductResult(item);
                        },
                    },
                },
            ];
        },
    });

    // Add CTRL/CMD + K key combination
    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
            event.preventDefault();
            setIsOpen(true);
        }
    });

    /**
     * Memoization used because we make 2 ajax calls: one for pages and one for products!
     * Cancel previous requests using AbortController: https://www.codetinkerer.com/2019/01/14/cancel-async-requests-pattern.html
     */
    const loadResults = memoize((query: string) => {
        abortController.abort(); // Cancel the previous request
        abortController = new AbortController();

        try {
            return requestAjax('Search', 'Autosuggest', {
                term: query,
            });
        } catch (ex: any) {
            if (ex.name === 'AbortError') {
                return; // Continuation logic has already been skipped, so return normally
            }

            throw ex;
        }
    });

    function isProductModule(module: string): boolean {
        return module === 'Commerce';
    }

    /**
     * Needed to get rid of &amp; etc
     */
    function decode(str: string): string {
        const s = '<div>' + str + '</div>';
        const e = document.createElement('decodeIt');
        e.innerHTML = s;
        return e.innerText;
    }

    function highlightHits(term: string, snippet: string): VNode {
        const snippetWithoutNewLines = snippet.replace(/[\r\n]+/g, ' ');
        const highlightedSnippet = decode(snippetWithoutNewLines).replace(
            new RegExp(term, 'gi'),
            (match) => `<strong>${match}</strong>`,
        );
        return html`${html([highlightedSnippet] as unknown as TemplateStringsArray)}`;
    }

    function renderPageResult(item: ForkSearchItem): VNode {
        return html`<a class="aa-ItemLink" href="${item.full_url}">
            <div className="aa-ItemContent">
                <div class="aa-ItemIcon aa-ItemIcon--noBorder">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M16.041 15.856c-0.034 0.026-0.067 0.055-0.099 0.087s-0.060 0.064-0.087 0.099c-1.258 1.213-2.969 1.958-4.855 1.958-1.933 0-3.682-0.782-4.95-2.050s-2.050-3.017-2.050-4.95 0.782-3.682 2.050-4.95 3.017-2.050 4.95-2.050 3.682 0.782 4.95 2.050 2.050 3.017 2.050 4.95c0 1.886-0.745 3.597-1.959 4.856zM21.707 20.293l-3.675-3.675c1.231-1.54 1.968-3.493 1.968-5.618 0-2.485-1.008-4.736-2.636-6.364s-3.879-2.636-6.364-2.636-4.736 1.008-6.364 2.636-2.636 3.879-2.636 6.364 1.008 4.736 2.636 6.364 3.879 2.636 6.364 2.636c2.125 0 4.078-0.737 5.618-1.968l3.675 3.675c0.391 0.391 1.024 0.391 1.414 0s0.391-1.024 0-1.414z"
                        ></path>
                    </svg>
                </div>
                <div class="aa-ItemContentBody">
                    <div class="aa-ItemContentTitle">${item.title}</div>
                </div>
            </div>
        </a>`;
    }

    function renderProductResult(item: ForkProductItem): VNode {
        return html`<a class="aa-ItemLink" href="${item.full_url}">
            <div className="aa-ItemContent">
                ${item.preview_image_url &&
                html`<div className="aa-ItemIcon aa-ItemIcon--picture aa-ItemIcon--alignTop">
                    <img src="${item.preview_image_url}" alt="${item.title}" width="100" height="100" />
                </div>`}

                <div className="aa-ItemContentBody">
                    <div className="aa-ItemContentTitle">${highlightHits(item.type!, item.title)}</div>

                    <div className="aa-ItemContentDescription">
                        ${highlightHits(item.type!, item.text)} <br />
                        ${item.price && html`<strong>€${item.price}</strong>`}
                    </div>
                </div>
            </div>
        </a>`;
    }
}
