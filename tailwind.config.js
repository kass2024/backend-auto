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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Rajdhani', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                military: {
                    50: '#f4f6ef',
                    100: '#e4e9d8',
                    200: '#c9d4b3',
                    300: '#a8b886',
                    400: '#8a9a5f',
                    500: '#6d7d47',
                    600: '#556332',
                    700: '#434f29',
                    800: '#384024',
                    900: '#2f3620',
                    950: '#181c10',
                },
                steel: {
                    50: '#f6f7f8',
                    100: '#eceef0',
                    200: '#d5d9de',
                    300: '#b0b8c1',
                    400: '#8591a0',
                    500: '#667384',
                    600: '#515c6c',
                    700: '#434b58',
                    800: '#3a404b',
                    900: '#333841',
                    950: '#22252b',
                },
            },
        },
    },

    plugins: [forms],
};
