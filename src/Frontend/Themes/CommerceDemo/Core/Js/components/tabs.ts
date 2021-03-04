const HASH_PREFIX = 'tab';

const menuActiveClass = ['active', 'text-gray-800'];
const tabPaneActiveClass = ['active', 'block'];
const tabPaneHiddenClass = ['hidden'];

/**
 * The tabs component adds behaviour to a simple navigational tab menu.
 * E.g. switching between Description, Specifications, ...
 */
export default (tabsSelector = '.js-tabs'): void => {
    // Find our tab menu and add event listeners
    const tabMenus = document.querySelectorAll(tabsSelector);
    [...tabMenus].forEach((tabMenu: Element) => {
        // Add click event listeners
        const tabButtons = tabMenu.querySelectorAll(`${tabsSelector} a.js-btn-tab`);
        if (tabButtons.length > 0) {
            [...tabButtons].forEach((button) => {
                button.addEventListener('click', (e: Event) => {
                    e.preventDefault();
                    showTab(e.currentTarget! as Element, tabMenu);
                    return false;
                });
            });
        }

        // Change to the correct tab based on the url hash (if set)
        if (window.location.hash) {
            const ref = window.location.hash.replace(`${HASH_PREFIX}_`, '');
            const button = document.querySelector(`${tabsSelector} a[href="${ref}"]`);
            const parentTabMenu = button?.parentElement?.parentElement;
            if (button && parentTabMenu) {
                showTab(button, parentTabMenu);
            }
        }
    });
};

function showTab(buttonElement: Element, tabMenu: Element, updateHash = true) {
    const targetTabId = buttonElement.getAttribute('href')!;

    // Start showing the tab on screen after click
    const tabContent = document.querySelector('.js-tab-content');
    const tabPaneTarget = tabContent?.querySelector(`.tab-pane${targetTabId}`);
    if (tabContent && tabPaneTarget) {
        // Remove active classes from currently showing tab, only from direct children!
        [...tabContent.querySelectorAll(`:scope > .tab-pane`)].forEach((tabPane) => {
            tabPane.classList.remove(...tabPaneActiveClass);
            tabPane.classList.add(...tabPaneHiddenClass);
        });

        // Activate the new tab
        tabPaneTarget.classList.add(...tabPaneActiveClass);
        tabPaneTarget.classList.remove(...tabPaneHiddenClass);

        // Mark button as active
        [...tabMenu.querySelectorAll('.active')].forEach((element) => element.classList.remove(...menuActiveClass));
        buttonElement.parentElement?.classList.add(...menuActiveClass);

        // Set the url hash
        if (updateHash) {
            window.location.hash = `${HASH_PREFIX}_${targetTabId.slice(1)}`;
        }
    }
}
