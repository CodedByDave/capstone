/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#4F46E5',
                'primary-foreground': '#FFFFFF',
                secondary: '#F9FAFB',
                background: '#F3F4F6',
                card: '#FFFFFF',
                border: '#E5E7EB',
                foreground: '#111827',
                'muted-foreground': '#6B7280',
            },
            fontFamily: {
                serif: ['"Playfair Display"', 'serif'],
            },
            animation: {
                float: 'float 4s ease-in-out infinite',
                marquee: 'marquee 25s linear infinite',
            },
            keyframes: {
                float: {
                    '0%,100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                marquee: {
                    '0%': { transform: 'translateX(0%)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
            },
        },
    },
    plugins: [],
};
