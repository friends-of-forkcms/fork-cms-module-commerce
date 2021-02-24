const webpack = require('webpack');
const exec = require('child_process').exec;
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
    output: {
        filename: '[name].[chunkhash].js', // Use hashed filenames based on changes, for long-term browser caching!
    },

    // Enable sourcemap generation
    devtool: 'source-map',

    module: {
        rules: [
            {
                test: /\.(css)$/,
                use: [
                    MiniCssExtractPlugin.loader, // This plugin extracts CSS into separate files.
                    'css-loader', // Interprets @import and url() just like import/require statements and resolves them.
                    // Apply PostCSS plugins defined in postcss.config.js
                    // Make sure to reference the correct postcss.config.js so we don't load one from node_modules
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                config: './postcss.config.js',
                            },
                        },
                    },
                ],
            },
            {
                test: /.*\.(gif|png|jpe?g|webp|svg|gif|ico|cur)$/i,
                use: [
                    {
                        loader: `img-optimize-loader`,
                        options: {
                            name: 'img/[name].[contenthash].[ext]',
                            compress: {
                                webp: true, // Transform png/jpg into webp files
                            },
                        },
                    },
                ],
            },
        ],
    },

    plugins: [
        // Lightweight CSS extraction plugin built on top of features available in Webpack (performance!).
        new MiniCssExtractPlugin({
            filename: '[name].[contenthash].css',
            chunkFilename: '[id].css',
        }),

        // Enable concatenation of the scope of modules into one closure for faster execution time (similar to Rollup "hoisting").
        // It enables the ability to concatenate the scope of all your modules into one closure and allow for your
        // code to have a faster execution time in the browser.
        new webpack.optimize.ModuleConcatenationPlugin(),

        // Anonymous Webpack plugin to copy css to the Core/Layout/Css/app.css location AFTER webpack finishes.
        // Make sure Fork CMS has a Core/Layout/Css/app.css file in the theme by copying the compiled app.*.css after compilation finishes.
        // This CSS file is used to load the theme styles in the backend in CKEditor.
        {
            apply: (compiler) => {
                compiler.hooks.afterEmit.tap('AfterEmitPlugin', (compilation) => {
                    const commands = [
                        'echo "Copying dist/app.*.css to Core/Layout/Css/screen.css..."',
                        'mkdir -p Core/Layout/Css && cp dist/app.*.css Core/Layout/Css/screen.css',
                        '([ -e "Core/Layout/Css/screen.css" ] && echo "Finished copying dist/app.*.css to Core/Layout/Css/screen.css" || (echo "ERROR: Failed copying app.css" && exit 1))',
                    ].join(' && ');
                    exec(commands, (err, stdout, stderr) => {
                        if (stdout) process.stdout.write(stdout);
                        if (stderr) process.stderr.write(stderr);
                    });
                });
            },
        },

        // Ignore all locale files of moment.js to save on bundlesize
        new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),

        // Pre-compress our static resources into .gz files so we can serve them up pre-compressed
        new CompressionPlugin(),
    ],

    optimization: {
        minimize: true,
        minimizer: ['...', new CssMinimizerPlugin()],
        splitChunks: {
            cacheGroups: {
                // Extract all non-dynamic imported node_modules imports into a vendor file. One of the potential advantages with splitting your vendor and application code is to enable long term caching
                // techniques to improve application loading performance. Since vendor code tends to change less often than the actual application code, the browser will be able to cache them separately,
                // and won't re-download them each time the app code changes.
                vendor: {
                    chunks: 'initial',
                    name: 'vendor',
                    test: /node_modules/,
                    enforce: true,
                },
            },
        },
    },
};
