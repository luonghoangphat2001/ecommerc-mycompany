/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary, #3b82f6)', // default to blue-500
        secondary: 'var(--color-secondary, #6b7280)', // default to gray-500
      },
      fontFamily: {
        sans: ['var(--font-primary)', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
