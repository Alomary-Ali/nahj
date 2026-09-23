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
                    50: '#F0FDFA',
                    100: '#CCFBF1',
                    200: '#99F6E4',
                    300: '#5EEAD4',
                    400: '#2DD4BF',
                    500: '#14B8A6',
                    600: '#0D9488',
                    700: '#0F766E', // Signature Nahj Emerald Petrol
                    800: '#115E59', // Hover
                    900: '#134E4A',
                    950: '#042F2E',
                    DEFAULT: '#0F766E',
                    hover: '#115E59',
                },
                gold: {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    200: '#FDE68A',
                    300: '#FCD34D',
                    400: '#FBBF24',
                    500: '#F59E0B',
                    600: '#D97706', // Royal Academic Gold
                    700: '#B45309',
                    800: '#92400E',
                    DEFAULT: '#D97706',
                },
                slate: {
                    850: '#151F32',
                },
                academic: {
                    success: '#0D9488',
                    warning: '#D97706',
                    gold: '#D97706',
                    danger: '#DC2626',
                    info: '#0F766E',
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
