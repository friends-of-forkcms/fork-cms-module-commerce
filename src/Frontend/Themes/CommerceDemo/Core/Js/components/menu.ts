type Menu = {
    activeDescendant: any;
    activeIndex: any;
    items: any[];
    open: boolean;
    init: (this: This) => void;
    focusButton: (this: This) => void;
    onButtonClick: (this: This) => void;
    onButtonEnter: (this: This) => void;
    onArrowUp: (this: This) => void;
    onArrowDown: (this: This) => void;
    onClickAway: (this: This, e: MouseEvent) => void;
};

type This = Menu & AlpineMagicProperties;

/**
 * Dropdown menu component in Alpine
 * Based on the menu Alpine component from https://tailwindui.com/js/components-v2.js
 */
export const menu = (): void => {
    window.Alpine.menu = function (e = { open: false }): Menu {
        return {
            activeDescendant: null,
            activeIndex: null,
            items: [],
            open: e.open,
            init(): void {
                this.items = Array.from(this.$el!.querySelectorAll('[role="menuitem"]'));
                this.$watch!('open', () => {
                    if (this.open) {
                        this.activeIndex = -1;
                    }
                });
            },

            focusButton() {
                this.$refs!.button.focus();
            },

            onButtonClick() {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick!(() => {
                        this.$refs!['menu-items'].focus();
                    });
                }
            },

            onButtonEnter() {
                this.open = !this.open;
                if (this.open) {
                    this.activeIndex = 0;
                    this.activeDescendant = this.items[this.activeIndex].id;
                    this.$nextTick!(() => {
                        this.$refs!['menu-items'].focus();
                    });
                }
            },

            onArrowUp() {
                if (!this.open) {
                    this.open = !0;
                    this.activeIndex = this.items.length - 1;
                    this.activeDescendant = this.items[this.activeIndex].id;
                }

                if (this.activeIndex !== 0) {
                    this.activeIndex = -1 === this.activeIndex ? this.items.length - 1 : this.activeIndex - 1;
                    this.activeDescendant = this.items[this.activeIndex].id;
                }
            },

            onArrowDown() {
                if (!this.open) {
                    this.open = !0;
                    this.activeIndex = 0;
                    this.activeDescendant = this.items[this.activeIndex].id;
                }

                if (this.activeIndex !== this.items.length - 1) {
                    this.activeIndex = this.activeIndex + 1;
                    this.activeDescendant = this.items[this.activeIndex].id;
                }
            },

            onClickAway(e) {
                if (this.open) {
                    const t = [
                        '[contentEditable=true]',
                        '[tabindex]',
                        'a[href]',
                        'area[href]',
                        'button:not([disabled])',
                        'iframe',
                        'input:not([disabled])',
                        'select:not([disabled])',
                        'textarea:not([disabled])',
                    ]
                        .map((e) => `${e}:not([tabindex='-1'])`)
                        .join(',');

                    this.open = false;

                    // @ts-ignore
                    e.target.closest(t) || this.focusButton();
                }
            },
        };
    };
};
