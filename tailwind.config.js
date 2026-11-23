/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
            colors: {
                // Milestone 1 Palette: Modern Luxury Tech / Architectural Aesthetic
                jet: {
                    DEFAULT: '#0D0D0D',
                    deep: '#080808',
                    base: '#0D0D0D',
                    surface: '#121212',
                    elevated: '#171717',
                    border: '#242424',
                },
                forest: {
                    DEFAULT: '#224F10',
                    deep: '#15330A',
                    base: '#224F10',
                    surface: '#1A3F0C',
                    light: '#2E6916',
                    muted: '#17360B',
                },
                lime: {
                    DEFAULT: '#B0E454',
                    accent: '#B0E454',
                    hover: '#C2ED77',
                    muted: '#8CB643',
                    faint: 'rgba(176, 228, 84, 0.15)',
                },
                ivory: {
                    DEFAULT: '#EAFFD0',
                    headline: '#EAFFD0',
                    soft: '#F4FFE6',
                    muted: '#C8DBB2',
                },
                // Preserved Studio Botanical Tokens (Required for Admin & Stress Tests)
                studio: {
                    offwhite: '#F9F8F6',
                    foliage: '#1C2A21',
                    'foliage-light': '#283c2f',
                    stone: '#E6E2DD',
                    'stone-dark': '#d8d3cd',
                    charcoal: '#1A1A1A',
                    muted: '#6E6B65',
                },
                // Preserved & Aliased Obsidian Tokens (Ensures test & backward compatibility)
                obsidian: {
                    DEFAULT: '#0D0D0D', // Aliased to Jet Black (legacy #0B0E0C)
                    legacy: '#0B0E0C',
                    deep: '#080B09',
                    base: '#0B0E0C',
                    surface: '#0E1310',
                    elevated: '#121814',
                },
                // Preserved & Aliased Moss Tokens (Ensures test & backward compatibility)
                moss: {
                    DEFAULT: '#224F10', // Aliased to Forest Green (legacy #1C3024)
                    legacy: '#1C3024',
                    deep: '#142018',
                    base: '#1C3024',
                    light: '#284433',
                    accent: '#B0E454', // Aliased to Lime Green (legacy #34D399)
                    emerald: '#34D399',
                },
            },
            fontFamily: {
                // Instrument Serif is primary display serif; Cormorant Garamond preserved for fallback & test assertions
                serif: ['"Instrument Serif"', '"Cormorant Garamond"', 'Georgia', 'serif'],
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
