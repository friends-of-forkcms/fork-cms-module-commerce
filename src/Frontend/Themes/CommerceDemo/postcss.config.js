module.exports = {
    plugins: {
        // PostCSS plugin to inline @import rules content
        'postcss-import': {},

        // Tailwind as a PostCSS plugin. Note: this needs to be after postcss-import!
        tailwindcss: {},

        // Add support for nested declarations like Sass has.
        'postcss-nested': {},

        autoprefixer: {},
    },
};
