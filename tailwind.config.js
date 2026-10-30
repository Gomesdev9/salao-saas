import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
        './app/Enums/**/*.php', // cores dos status da agenda
    ],

    theme: {
        extend: {
            // Paleta do design (EC Clinic Beauty)
            colors: {
                creme: '#FEFAF3', // fundo das páginas
                cartao: '#FFFBF6', // fundo dos cards e campos
                areia: '#F7F1E7', // cabeçalho de tabelas
                caramelo: '#BC7F5C', // destaque
                cafe: {
                    DEFAULT: '#925837', // botões
                    escuro: '#78472A',
                },
                tinta: '#1C0D06', // texto e menu lateral
                suave: '#6F5947', // texto secundário
                linha: {
                    DEFAULT: '#E7E0D8',
                    forte: '#DBD3CB',
                    clara: '#F2EBE2',
                },
                pele: '#F9E3D5', // avatar com iniciais
                mel: { DEFAULT: '#FCF0E1', texto: '#6B4210' }, // avisos suaves
                menu: { ativo: '#35251B', texto: '#E3D6C8', suave: '#BFAA93' },
                perigo: '#A3312B',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Playfair Display"', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            borderRadius: {
                campo: '14px',
                cartao: '22px',
                menu: '30px',
            },
            boxShadow: {
                cartao: '0 1px 2px rgba(28, 13, 6, 0.05), 0 12px 32px -14px rgba(28, 13, 6, 0.22)',
                menu: '0 2px 6px rgba(28, 13, 6, 0.12), 0 28px 56px -24px rgba(28, 13, 6, 0.6)',
            },
        },
    },

    plugins: [forms],
};
