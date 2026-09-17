import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#00687A',
                    hover: '#00505E',
                    light: '#E6F0F2',
                    accent: '#00839B',
                },
            },
            fontFamily: {
                sans: ['"Century Gothic"', 'CenturyGothic', 'AppleGothic', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'glow': '0 0 25px -5px rgba(0, 104, 122, 0.15)',
            },
        },
    },

    plugins: [],
};