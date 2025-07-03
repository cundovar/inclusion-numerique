import { createApp } from 'vue';
import './style.css';

import App from './App.vue';
import router from './router';
import { registerSW } from 'virtual:pwa-register'

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

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js')
      .then(reg => console.log('Service Worker enregistré', reg.scope))
      .catch(err => console.error(' Erreur SW', err));
  }