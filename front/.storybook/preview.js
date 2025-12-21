import { setup } from '@storybook/vue3';

// Import Tailwind CSS and global styles
import '../src/style.css';

// Font Awesome
import '@fortawesome/fontawesome-free/css/all.css';

// PrimeIcons
import 'primeicons/primeicons.css';

// Element Plus
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';

// Setup Element Plus globally for all stories
setup((app) => {
  app.use(ElementPlus);
});

/** @type { import('@storybook/vue3-vite').Preview } */
const preview = {
  parameters: {
    controls: {
      matchers: {
       color: /(background|color)$/i,
       date: /Date$/i,
      },
    },

    a11y: {
      // 'todo' - show a11y violations in the test UI only
      // 'error' - fail CI on a11y violations
      // 'off' - skip a11y checks entirely
      test: "todo"
    }
  },
};

export default preview;