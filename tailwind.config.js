module.exports = {
    purge: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
  darkMode: false, // or 'media' or 'class'
  theme: {
      aspectRatio: {
          none: 0,
          square: [1, 1],
          "16/9": [16, 9],
          "4/3": [4, 3],
          "21/9": [21, 9]
      },
    extend: {},
  },
  variants: {
      aspectRatio: ['responsive'],
    extend: {},
  },
  plugins: [
      require("tailwindcss-responsive-embed"),
      require('@tailwindcss/aspect-ratio'),
  ],
};
