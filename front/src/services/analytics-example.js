/**
 * EXEMPLE D'UTILISATION du UserAnalyticsService
 * 
 * Ce fichier montre comment intégrer et utiliser le service d'analytics
 * dans ton application Vue.js/Symfony
 */

// Import du service (si tu uses les modules ES6)
import UserAnalyticsService from './userAnalyticsService.js';

// OU si tu l'inclus directement dans le HTML :
// <script src="./userAnalyticsService.js"></script>
// const analytics = new window.UserAnalyticsService();

/**
 * ÉTAPE 1 : Initialisation dans ton App.vue ou main.js
 */
const analytics = new UserAnalyticsService();

// Pour debug pendant le développement
analytics.debug();

/**
 * ÉTAPE 2 : Tracking automatique dans Vue Router
 * 
 * Ajoute ça dans ton router/index.js pour tracker automatiquement 
 * chaque changement de page
 */
/*
import { createRouter } from 'vue-router';

const router = createRouter({
  // ... tes routes
});

// Track chaque changement de route
router.afterEach((to, from) => {
  // Attendre que la page soit rendue
  setTimeout(() => {
    analytics.trackPageView(to.path, to.meta.title || to.name);
  }, 100);
});
*/

/**
 * ÉTAPE 3 : Tracking des QCM dans tes composants Vue
 */

// Dans un composant QCM :
/*
export default {
  name: 'QCMComponent',
  methods: {
    startQCM(qcmId, category) {
      // Track le début du QCM
      this.analytics.trackQCM(qcmId, 'start', { category });
    },
    
    answerQuestion(qcmId, questionId, answer) {
      // Track chaque réponse
      this.analytics.trackQCM(qcmId, 'answer', { 
        questionId, 
        answer,
        timestamp: Date.now()
      });
    },
    
    completeQCM(qcmId, score, timeSpent) {
      // Track la fin du QCM
      this.analytics.trackQCM(qcmId, 'complete', { 
        score, 
        timeSpent,
        category: this.qcmCategory 
      });
    },
    
    abandonQCM(qcmId) {
      // Track l'abandon
      this.analytics.trackQCM(qcmId, 'abandon', {
        progress: this.currentQuestionIndex / this.totalQuestions
      });
    }
  },
  
  // Inject le service dans tes composants
  inject: ['analytics']
}
*/

/**
 * ÉTAPE 4 : Tracking d'actions spécifiques
 */

// Exemples d'utilisation dans tes composants :

// Track un téléchargement
function trackDownload(fileName) {
  analytics.trackAction('download', fileName, {
    fileType: fileName.split('.').pop(),
    fileSize: null // Tu peux ajouter la taille si tu l'as
  });
}

// Track l'utilisation d'une fonctionnalité
function trackFeatureUsage(featureName, details = {}) {
  analytics.trackAction('feature_use', featureName, details);
}

// Track les erreurs utilisateur
function trackUserError(errorType, message) {
  analytics.trackAction('user_error', errorType, {
    message: message,
    userAgent: navigator.userAgent,
    url: window.location.href
  });
}

// Track les interactions avec le menu
function trackMenuClick(menuItem) {
  analytics.trackAction('menu_click', menuItem);
}

// Track la recherche
function trackSearch(query, resultsCount) {
  analytics.trackAction('search', 'search_performed', {
    query: query.substring(0, 50), // Limite pour la vie privée
    resultsCount: resultsCount
  });
}

/**
 * ÉTAPE 5 : Intégration dans Vue.js (main.js)
 */
/*
import { createApp } from 'vue';
import App from './App.vue';
import UserAnalyticsService from './services/userAnalyticsService.js';

const app = createApp(App);

// Créer une instance globale du service analytics
const analytics = new UserAnalyticsService();

// Le rendre disponible dans tous les composants
app.provide('analytics', analytics);

// Ou l'ajouter aux propriétés globales
app.config.globalProperties.$analytics = analytics;

app.mount('#app');
*/

/**
 * ÉTAPE 6 : Utilisation dans les composants Vue
 */
/*
<template>
  <div>
    <button @click="handleButtonClick">Mon Bouton</button>
    <form @submit="handleFormSubmit">
      <!-- formulaire -->
    </form>
  </div>
</template>

<script>
export default {
  name: 'MonComposant',
  
  // Si tu uses provide/inject
  inject: ['analytics'],
  
  methods: {
    handleButtonClick() {
      // Track le clic
      this.analytics.trackAction('button_click', 'mon-bouton-special');
      
      // ... rest de ta logique
    },
    
    handleFormSubmit() {
      // Track la soumission de formulaire
      this.analytics.trackAction('form_submit', 'contact-form');
      
      // ... rest de ta logique
    }
  }
}
</script>
*/

/**
 * ÉTAPE 7 : Méthodes utiles à connaître
 */

// Obtenir les stats de la session courante
const stats = analytics.getSessionStats();
console.log('Stats session:', stats);

// Forcer la sauvegarde des données
analytics.saveSessionData();

// Révoquer le consentement (pour tester)
// analytics.revokeConsent();

// Accorder le consentement manuellement (pour tester)
// analytics.grantConsent();

// Debug complet
analytics.debug();

/**
 * ÉTAPE 8 : Exemples de tracking avancé
 */

// Track le temps passé sur une section spécifique
let sectionStartTime = Date.now();

function trackSectionTime(sectionName) {
  const timeSpent = Date.now() - sectionStartTime;
  analytics.trackAction('section_time', sectionName, {
    timeSpent: timeSpent,
    timeSpentSeconds: Math.round(timeSpent / 1000)
  });
}

// Track les erreurs JavaScript
window.addEventListener('error', (event) => {
  analytics.trackAction('javascript_error', 'runtime_error', {
    message: event.message,
    filename: event.filename,
    line: event.lineno,
    column: event.colno
  });
});

// Track les interactions avec des éléments spécifiques
document.addEventListener('click', (event) => {
  // Track clics sur images
  if (event.target.tagName === 'IMG') {
    analytics.trackAction('image_click', event.target.src);
  }
  
  // Track clics sur liens de navigation
  if (event.target.closest('.nav-link')) {
    analytics.trackAction('nav_click', event.target.textContent);
  }
});

/**
 * ÉTAPE 9 : Configuration pour différents environnements
 */

// Tu peux modifier la config selon l'environnement
if (process.env.NODE_ENV === 'development') {
  // En développement, log plus d'infos
  analytics.config.debug = true;
} else {
  // En production, être plus discret
  analytics.config.debug = false;
}

/**
 * ÉTAPE 10 : Nettoyage et optimisation
 */

// Nettoyer les anciennes données (à faire périodiquement)
function cleanOldAnalyticsData() {
  // Logique pour nettoyer les vieilles données
  // (à implémenter selon tes besoins)
}

// Exporter pour utilisation
export {
  analytics,
  trackDownload,
  trackFeatureUsage,
  trackUserError,
  trackMenuClick,
  trackSearch
};