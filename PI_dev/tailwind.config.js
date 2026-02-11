/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.html.twig",
    "./public/**/*.js",
    "./src/**/*.php"
  ],
  theme: {
    extend: {
      fontFamily: {
        urbanist: ['Urbanist', 'sans-serif'],
      },
      colors: {
        neutral: {
          300: '#d1d5db',
        },
      },
    },
  },
  plugins: [],
}
