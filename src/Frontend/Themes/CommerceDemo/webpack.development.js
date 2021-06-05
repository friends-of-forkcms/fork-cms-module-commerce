const path = require('path');
const webpack = require('webpack');
const chokidar = require('chokidar');

const publicPath = `/src/Frontend/Themes/${path.basename(__dirname)}/dist/`;

module.exports = {
    // Enable cheap sourcemap generation in dev.
    devtool: 'eval-cheap-module-source-map',

    module: {
        rules: [
            {
                test: /\.(css)$/,
                use: [
                    'style-loader', // Use inline-css styles
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
                test: /.*\.(gif|png|jpe?g|svg|gif|ico|cur)$/i,
                use: [
                    {
                        loader: 'url-loader',
                        options: {
                            limit: 1000, // If file is >1k, it ends in the dist folder instead of being used base64 inline.
                            name: 'img/[name].[ext]',
                        },
                    },
                ],
            },
        ],
    },

    // Configuration for the webpack-dev-server
    devServer: {
        // Use Chokidar as file-watcher. CSS and JS injection handled by HMR and when a view changes,
        // devserver makes the browser reload automatically. See: https://stackoverflow.com/a/52476173/1409047
        before(app, server) {
            chokidar
                .watch([
                    './Core/Layout/Css/**/*.css',
                    './Core/Layout/Templates/**/*.html.twig',
                    './Core/Layout/Templates/**/*.html',
                    './Modules/**/*.html.twig',
                ])
                .on('all', function () {
                    server.sockWrite(server.sockets, 'content-changed');
                });
        },
        host: 'localhost', // Make sure we can access the server via any (mobile) device for testing
        port: 3000,
        proxy: {
            '**': {
                target: 'https://fork-cms-module-commerce-demo.test/',
                secure: false,
                changeOrigin: true, // Needed for images by LiipImagineBundle to work.
                headers: {
                    'Access-Control-Allow-Origin': '*',
                    'X-Webpack-Dev-Server': 'true',
                },
            },
        },
        publicPath,
        contentBase: path.resolve(__dirname, 'dist'),
        historyApiFallback: true,
        inline: true, // Inline mode is recommended for Hot Module Replacement as it includes an HMR trigger from the websocket
        hot: true, // WDS has to run in hot mode to expose hot module replacement interface to the client.
        overlay: true, // Shows a full-screen overlay in the browser when there are compiler errors or warnings
    },
    plugins: [new webpack.HotModuleReplacementPlugin()],
};
