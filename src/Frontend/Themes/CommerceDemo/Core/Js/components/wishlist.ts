import Alpine from 'alpinejs';

interface WishlistStore {
    ids: number[];
    add: (id: number) => void;
    remove: (id: number) => void;
    has: (id: number) => boolean;
}

/**
 * Alpine.js logic to handle adding/removing an item to the wishlist.
 */
export const wishlist = (): void => {
    const COOKIE_NAME = 'wishlist-ids';

    /**
     * Global store for our wishlist ids
     */
    Alpine.store<WishlistStore>('wishlist', {
        ids: JSON.parse(getCookie(COOKIE_NAME) || '[]'),

        add(id: number): void {
            if (!this.has(id)) {
                this.ids = [...this.ids, id];
            }
        },

        remove(id: number): void {
            this.ids = this.ids.filter((i) => i !== id);
        },

        has(id: number): boolean {
            return this.ids.indexOf(id) !== -1;
        },
    });

    /**
     * Watch the wishlist ids and write every change to the cookie.
     */
    Alpine.effect(() => {
        const wishlistIds = Alpine.store<WishlistStore>('wishlist').ids;
        setCookie(COOKIE_NAME, JSON.stringify(wishlistIds));
    });

    /**
     * Alpine.js component for a single product node. Handles the heart button.
     */
    Alpine.data('wishlistItem', (productId: number) => ({
        wishlist: Alpine.store<WishlistStore>('wishlist'),
        isAdded: false,

        init(): void {
            this.isAdded = this.wishlist.has(productId);
        },

        addItemToWishlist(): void {
            if (!this.isAdded) {
                this.wishlist.add(productId);
                this.isAdded = true;
            }
        },

        removeItemFromWishlist(): void {
            this.wishlist.remove(productId);
            this.isAdded = false;
        },
    }));

    function getCookie(name): string | undefined {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return parts.pop()!.split(';').shift();
        }
        return undefined;
    }

    function setCookie(name: string, value: string, duration = 60 * 60 * 24 * 30): void {
        document.cookie = `${name}=${value}; path=/; max-age=${duration}; SameSite=Strict; Secure;`;
    }
};
