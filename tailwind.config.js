import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#EBF3FF',
                    100: '#D6E4FF',
                    200: '#ADC8FF',
                    300: '#85A5FF',
                    400: '#597EF7',
                    500: '#2F54EB',
                    600: '#0056D2', // Coursera Official Primary Blue
                    700: '#00419E', // Hover
                    800: '#002C6E',
                    900: '#001944',
                    DEFAULT: '#0056D2',
                    hover: '#00419E',
                },
                slate: {
                    850: '#151F32',
                },
                academic: {
                    success: '#0F8458',
                    warning: '#D97706',
                    danger: '#DC2626',
                    info: '#0056D2',
                },
            },
            fontFamily: {
                sans: ['Inter', 'IBM Plex Sans Arabic', 'Cairo', ...defaultTheme.fontFamily.sans],
                arabic: ['IBM Plex Sans Arabic', 'Cairo', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
