import { createApp } from 'vue';
import './style.css';
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import App from './App.vue';
import router from './router';
import { registerSW } from 'virtual:pwa-register'
import axios from 'axios'

// Font Awesome
import '@fortawesome/fontawesome-free/css/all.css'

// Element Plus
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'

// primsjs Charge les langages dont tu as besoin
import Prism from 'prismjs';
import 'prismjs/themes/prism-okaidia.css';
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-markup';

// Configuration globale d'Axios
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const app = createApp(App)
app.use(router)
app.use(createPinia().use(piniaPluginPersistedstate))
app.use(ElementPlus)
app.mount('#app')
registerSW({
    onNeedRefresh() {
      console.log("Une nouvelle version est dispo, recharger la page !");
    },
    onOfflineReady() {
      console.log("L'application est prête à fonctionner hors ligne !");
    }
  });
  window.addEventListener('offline', () => {
    console.warn('🔌 Vous êtes hors ligne !');
  });
  
  window.addEventListener('online', () => {
    console.log('✅ Connexion rétablie.');
  });

