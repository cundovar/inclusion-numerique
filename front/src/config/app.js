export const APP_CONFIG = {
  API_BASE_URL: import.meta.env.DEV ? 'http://localhost:8080' : '',
  ADMIN_URL: import.meta.env.DEV ? 'http://localhost:8080/page-content' : '/page-content',
  PWA: {
    APP_NAME: 'SPA Hybride Vue Symfony',
    SHORT_NAME: 'SpaVueSymfo',
    DESCRIPTION: 'Application hybride combinant Vue.js et Symfony pour une expérience utilisateur optimale',
    THEME_COLOR: '#3b82f6',
    BACKGROUND_COLOR: '#ffffff'
  }
}