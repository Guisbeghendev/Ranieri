import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import flowbite from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './node_modules/flowbite/**/*.js', // Adicionado para Flowbite
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                roxo1: '#61152d',
                roxo2: '#681630',
                preto1: '#0f0e0f',
                laranja1: '#c89c20',
                laranja2: '#cd862a',
                laranja3: '#c9583e',
                verde1: '#2e5935',
                azul1: '#1b365d',
                amarelo1: '#c5a100',
                vermelho1: '#9e1b1b',
                prata1: '#d1d1d1',
                branco1: '#f8f8f8',
                rosa1: '#a54161',
                dourado1: '#bfa14f',
                cinza1: '#8a8a8a',
            },
        },
    },

    plugins: [
        forms,
        flowbite, // Plugin do Flowbite adicionado aqui
    ],
};
