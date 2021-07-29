export const memoize = (fn: any): any => {
    const cache: any = {};
    return (...args: any[]) => {
        const n = args[0];
        if (n in cache) {
            return cache[n];
        } else {
            const result = fn(n);
            cache[n] = result;
            return result;
        }
    };
};

export const ucfirst = (word: string) => {
    return word.charAt(0).toUpperCase() + word.slice(1);
}
