/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/**/*.tsx",
  ],
  theme: {
    extend: {
      colors: {
        'lime-neon': '#c6ff00',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
      },
      boxShadow: {
        'lime': '0 4px 24px 0 rgba(198, 255, 0, 0.25)',
      },
      keyframes: {
        'pulse-lime': {
          '0%, 100%': { boxShadow: '0 0 0 0 rgba(198,255,0,0.4)' },
          '50%': { boxShadow: '0 0 0 6px rgba(198,255,0,0)' },
        }
      },
      animation: {
        'pulse-lime': 'pulse-lime 2s ease-in-out infinite',
      }
    },
  },
  plugins: [],
}
