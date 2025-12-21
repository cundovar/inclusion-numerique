/** @type {import('tailwindcss').Config} */
import plugin from 'tailwindcss/plugin';
export default {
    content: [
      "./index.html",
      "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],

    safelist: [
      // Background colors
      { pattern: /^bg-(blue|green|red|yellow|purple|pink|indigo|gray|orange|teal|cyan)-(100|200|300|400|500|600|700|800|900)$/ },
      // Gradient directions
      { pattern: /^bg-gradient-to-(t|tr|r|br|b|bl|l|tl)$/ },
      // Gradient from/to colors (catégories dynamiques)
      { pattern: /^from-(blue|green|red|yellow|purple|pink|indigo|gray|orange|teal|cyan)-(100|200|300|400|500|600|700|800|900)$/ },
      { pattern: /^to-(blue|green|red|yellow|purple|pink|indigo|gray|orange|teal|cyan)-(100|200|300|400|500|600|700|800|900)$/ },
      // Text colors
      { pattern: /^text-(white|black|blue|green|red|yellow|purple|pink|indigo|gray|orange|teal|cyan)-(100|200|300|400|500|600|700|800|900)?$/ },
      // Text sizes
      { pattern: /^text-(xs|sm|base|lg|xl|2xl|3xl|4xl)$/ },
      // Hover background colors
      { pattern: /^hover:bg-(blue|green|red|yellow|purple|pink|indigo|gray|orange|teal|cyan)-(100|200|300|400|500|600|700|800|900)$/ },
      // Hover text colors
      { pattern: /^hover:text-(white|black|blue|green|red|yellow|purple|pink|indigo|gray|orange|teal|cyan)-(100|200|300|400|500|600|700|800|900)?$/ },
    ],

    theme: {
      extend: {},
    },
    plugins: [
      require('tailwind-scrollbar'),
    ],
  }
  
