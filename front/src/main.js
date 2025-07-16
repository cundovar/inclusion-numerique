import { createApp } from 'vue';
import './style.css';

import App from './App.vue';
import router from './router';
import { registerSW } from 'virtual:pwa-register'
import axios from 'axios'

// Font Awesome
import '@fortawesome/fontawesome-free/css/all.css'

// primsjs Charge les langages dont tu as besoin
import Prism from 'prismjs';
import 'prismjs/themes/prism-okaidia.css';
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-markup';

// Configuration globale d'Axios
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

createApp(App).use(router).mount('#app');
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

