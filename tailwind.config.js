import aspectRatio from '@tailwindcss/aspect-ratio';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {},
    },
    variants: {
        aspectRatio: ['responsive'],
        extend: {},
    },
    plugins: [
        aspectRatio,
    ],
};
