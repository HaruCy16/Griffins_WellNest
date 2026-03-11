/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{html,php,js}"],
  theme: {
    extend: {
      colors: {
        // Brand Colors from Client
        'cool-white': '#F4FDFF',
        'bronze': {
          DEFAULT: '#825E2F',
          50: '#F5EDE4',
          100: '#E8D9C8',
          200: '#D4BC9D',
          300: '#C09F72',
          400: '#A37D4A',
          500: '#825E2F',
          600: '#6B4E27',
          700: '#543D1F',
          800: '#3D2C16',
          900: '#261B0E',
        },
        'body-gray': '#545454',
        'golden': {
          DEFAULT: '#F6C604',
          50: '#FEF9E7',
          100: '#FDF2C4',
          200: '#FCE88D',
          300: '#FADD56',
          400: '#F8D21F',
          500: '#F6C604',
          600: '#C49E03',
          700: '#937603',
          800: '#624F02',
          900: '#312701',
        },
        'electric-blue': {
          DEFAULT: '#035096',
          50: '#E6F0F9',
          100: '#B3D4ED',
          200: '#80B8E1',
          300: '#4D9CD5',
          400: '#1A80C9',
          500: '#035096',
          600: '#024078',
          700: '#02305A',
          800: '#01203C',
          900: '#01101E',
        },
      },
    },
  },
  plugins: [],
}