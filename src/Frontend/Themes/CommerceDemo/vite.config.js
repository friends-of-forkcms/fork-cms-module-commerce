import * as path from 'path';
import ViteRestart from 'vite-plugin-restart';
import checker from 'vite-plugin-checker';
import copy from 'rollup-plugin-copy';

const FORK_THEME_BASE_PATH = '/src/Frontend/Themes/' + path.basename(__dirname);

/**
 * @type {import('vite').UserConfig}
 */
export default ({ command }) => ({
    base: FORK_THEME_BASE_PATH + (command === 'serve' ? '/' : '/dist/'),
    build: {
        manifest: true,
        outDir: './dist/',
        rollupOptions: {
            input: {
                app: '/Core/Js/app.ts',
            },
        },
    },

    plugins: [
        // Reload the Vite server when our (twig) templates changed
        ViteRestart({
            reload: ['Core/Layout/Templates/**/*', 'Modules/**/*'],
        }),

        // A Vite plugin that can run TypeScript checks in worker thread.
        checker({ typescript: true }),

        // Ensure a screen.css file exists (which gets loaded by the CMS)
        copy({
            hook: 'writeBundle',
            targets: [{ src: 'dist/assets/app.*.css', dest: 'Core/Layout/Css', rename: () => 'screen.css' }],
        }),
    ],

    // We need to make a few tweaks to still serve from the local webserver
    server: {
        host: '0.0.0.0', // broadcast to all ipv4 addresses on the local machine (e.g. for Docker setups)
        cors: true,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
    },
});
