import DefaultTheme from 'vitepress/theme'
import Status from './components/Status.vue'
import ThemeGrid from './components/ThemeGrid.vue'
import './custom.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    // Disponibles dans toutes les pages Markdown
    app.component('Status', Status)
    app.component('ThemeGrid', ThemeGrid)
  }
}
