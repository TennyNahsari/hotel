/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        forest: {
          DEFAULT: '#173F35',
          50: '#F2F7F5',
          100: '#E1ECE8',
          200: '#C0D7D0',
          300: '#94BBB0',
          400: '#5C9384',
          500: '#347162',
          600: '#23594D',
          700: '#173F35',
          800: '#112F28',
          900: '#0B1F1A',
        },
        sand: {
          DEFAULT: '#D8C3A5',
          50: '#FAF7F3',
          100: '#F4ECE1',
          200: '#E8D7C3',
          300: '#D8C3A5',
          400: '#C7AC85',
          500: '#B39366',
          600: '#95774B',
          700: '#735B39',
          800: '#513F27',
          900: '#302517',
        },
        gold: {
          DEFAULT: '#B8945A',
          light: '#D4B886',
          dark: '#8C6C36',
        },
        ivory: '#F8F5EF',
        charcoal: '#252525',
        taupe: '#756F67',
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#173F35',
          700: '#112F28',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
      },
      fontFamily: {
        serif: ['"Playfair Display"', 'Georgia', 'serif'],
        sans: ['Inter', 'sans-serif'],
      },
      spacing: {
        '128': '32rem',
        '144': '36rem',
      },
    },
  },
  plugins: [],
}
