/*

Tailwind - The Utility-First CSS Framework

A project by Adam Wathan (@adamwathan), Jonathan Reinink (@reinink),
David Hemphill (@davidhemphill) and Steve Schoger (@steveschoger).

Welcome to the Tailwind config file. This is where you can customize
Tailwind specifically for your project. Don't be intimidated by the
length of this file. It's really just a big JavaScript object and
we've done our very best to explain each section.

View the full documentation at https://tailwindcss.com.
*/
const colors = require('tailwindcss/colors');

// See defaults: https://github.com/tailwindcss/tailwindcss/blob/master/stubs/defaultConfig.stub.js
module.exports = {
    purge: {
        content: [
            'Core/Layout/Templates/**/*.{twig,html}',
            'Core/Js/**/*.{js,jsx,ts,tsx}',
            'Modules/*/Layout/{Templates,Widgets}/**/*.{twig,html}',
        ],

        // https://github.com/FullHuman/purgecss-docs/blob/master/whitelisting.md
        // Also add the .content and .editor css classes we define to use in the Fork CMS editor styles.
        options: {
            safelist: {
                deep: [/^content|^editor/],
            },
        },
    },
    theme: {
        fontFamily: {
            sans: [
                'Inter',
                '-apple-system',
                'BlinkMacSystemFont',
                'Segoe UI',
                'Roboto',
                'Oxygen-Sans',
                'Ubuntu',
                'Cantarell',
                'Helvetica Neue',
                'sans-serif',
            ],
        },
        extend: {
            colors: {
                gray: colors.trueGray,
            },
        },
    },
};
