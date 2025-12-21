import { mergeConfig } from 'vite';

/** @type { import('@storybook/vue3-vite').StorybookConfig } */
const config = {
  stories: [
    "../src/**/*.mdx",
    "../src/**/*.stories.@(js|jsx|mjs|ts|tsx)"
  ],
  addons: [
    "@chromatic-com/storybook",
    "@storybook/addon-a11y",
    "@storybook/addon-docs",
    "@storybook/addon-onboarding"
  ],
  framework: {
    name: "@storybook/vue3-vite",
    options: {},
  },
  viteFinal: async (config) => {
    return mergeConfig(config, {
      define: {
        'process.env': {},
      },
      server: {
        host: '0.0.0.0',
        port: 6006,
        watch: {
          usePolling: true,
          interval: 500
        }
      },
    });
  }
};
export default config;
