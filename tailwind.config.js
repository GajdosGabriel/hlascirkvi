module.exports = {
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
      require('@tailwindcss/aspect-ratio'),
  ],
};
