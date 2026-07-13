module.exports = {
    root: true,
    env: {
        browser: true,
        es2022: true,
    },
    extends: ['eslint:recommended'],
    ignorePatterns: ['assets/main-*.js', 'assets/workbox-*.js', 'public/build/**'],
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
    },
    rules: {
        'no-console': 'off',
    },
};
