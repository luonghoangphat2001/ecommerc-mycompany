/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary, #004687)',
        secondary: 'var(--color-secondary, #FBAE17)',
        brand: {
          navy: '#004687',
          navyHover: '#003669',
          dark: '#09253E',
          gold: '#FBAE17',
          goldHover: '#E59C0D',
          goldLight: '#FFF8E6',
          red: '#DC2626',
          bg: '#F8FAFC',
          surface: '#FFFFFF',
          border: '#E2E8F0',
        }
      },
      fontFamily: {
        sans: [
          'var(--font-primary, "Be Vietnam Pro", "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif)',
          'sans-serif'
        ],
        mono: [
          '"JetBrains Mono"',
          'ui-monospace',
          'SFMono-Regular',
          'Menlo',
          'Monaco',
          'Consolas',
          'monospace'
        ],
      },
      boxShadow: {
        'card': '0 1px 2px 0 rgba(15, 23, 42, 0.04)',
        'card-hover': '0 8px 20px -4px rgba(15, 23, 42, 0.07), 0 2px 6px -2px rgba(15, 23, 42, 0.04)',
        'modal': '0 20px 35px -10px rgba(9, 37, 62, 0.16), 0 8px 14px -6px rgba(9, 37, 62, 0.08)',
      },
      transitionTimingFunction: {
        'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
      }
    },
  },
  plugins: [],
}
