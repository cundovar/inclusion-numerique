import DefaultTheme from 'vitepress/theme'
import Status from './components/Status.vue'
import './custom.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    // Disponible dans toutes les pages Markdown : <Status is="ci" />
    app.component('Status', Status)
  }
}
