import js from '@eslint/js'
import vue from 'eslint-plugin-vue'
import globals from 'globals'

export default [
  {
    ignores: [
      'dist/',
      'dev-dist/',
      'node_modules/',
      'storybook-static/',
      'coverage/',
      'public/',
      'build-output',
      '.vite'
    ],
  },

  js.configs.recommended,

  ...vue.configs['flat/recommended'],

  {
    files: ['**/*.{js,mjs,cjs,vue}'],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
  },
]