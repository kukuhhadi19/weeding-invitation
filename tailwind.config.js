import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Lexend Deca"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#165DFF',
                    hover: '#0E4BD9',
                },
                foreground: '#080C1A',
                secondary: '#6A7686',
                border: '#F3F4F3',
                muted: '#EFF2F7',
                'card-grey': '#F1F3F6',
                success: '#30B22D',
                error: '#ED6B60',
                warning: '#FED71F',
            },
        },
    },

    plugins: [forms],
};
