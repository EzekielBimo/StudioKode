/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
            colors: {
                studio: {
                    offwhite: '#F9F8F6',
                    foliage: '#1C2A21',
                    'foliage-light': '#283c2f',
                    stone: '#E6E2DD',
                    'stone-dark': '#d8d3cd',
                    charcoal: '#1A1A1A',
                    muted: '#6E6B65',
                },
                obsidian: {
                    DEFAULT: '#0B0E0C',
                    deep: '#080B09',
                    base: '#0B0E0C',
                    surface: '#0E1310',
                    elevated: '#121814',
                },
                moss: {
                    DEFAULT: '#1C3024',
                    deep: '#142018',
                    base: '#1C3024',
                    light: '#284433',
                    accent: '#34D399',
                },
            },
            fontFamily: {
                serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
            },
            letterSpacing: {
                'widest-plus': '0.25em',
            },
            backdropBlur: {
                xs: '2px',
            },
            boxShadow: {
                xs: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            },
        },
    },
    plugins: [],
};
