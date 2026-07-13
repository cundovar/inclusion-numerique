const js = require('@eslint/js');

module.exports = [
    {
        ignores: ['assets/main-*.js', 'assets/workbox-*.js', 'public/build/**'],
    },
    js.configs.recommended,
    {
        files: ['assets/**/*.js'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
        },
        rules: {
            'no-console': 'off',
        },
    },
];
