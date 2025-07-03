/** @type {import('tailwindcss').Config} */
import plugin from 'tailwindcss/plugin';
export default {
    content: [
      "./index.html",
      "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
   
    theme: {
      extend: {},
    },
    plugins: [
      require('tailwind-scrollbar'),
    ],
  }
  