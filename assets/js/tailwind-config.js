tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Hind Siliguri"', 'sans-serif'],
                serif: ['"Noto Serif Bengali"', 'serif'],
                anek: ['"Anek Bangla"', 'sans-serif'],
            },
            colors: {
                brand: {
                    900: '#0a0a0a',
                    800: '#171717',
                    700: '#262626',
                    gold: '#cda873',
                    gold_light: '#e6c89b',
                    light: '#fafafa',
                }
            },
            animation: {
                'float': 'float 6s ease-in-out infinite',
                'slide-up': 'slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(40px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                }
            }
        }
    }
}