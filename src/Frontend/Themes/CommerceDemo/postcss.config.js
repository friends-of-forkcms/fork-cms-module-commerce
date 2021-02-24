module.exports = {
    plugins: [
        // PostCSS Preset Env lets you convert modern CSS into something most browsers can understand,
        // determining the polyfills you need based on your targeted browsers or runtime environments.
        require('postcss-preset-env')({
            browsers: ['last 2 versions', '> 5% in BE'],
            features: {
                'focus-within-pseudo-class': false, // @see https://github.com/tailwindlabs/tailwindcss/discussions/2462
            },
        }),

        // PostCSS plugin to inline @import rules content
        require('postcss-import'),

        // Tailwind as a PostCSS plugin. Note: this needs to be after postcss-import!
        // @see https://tailwindcss.com/docs/using-with-preprocessors
        require('tailwindcss'),

        // Add support for nested declarations like Sass.
        require('postcss-nested'),
    ],
};
