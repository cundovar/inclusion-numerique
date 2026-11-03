// Règle du service worker pour la lecture de l'API (cours, menus, QCM), importée par vite.config.js.
// Workbox recopie les fonctions telles quelles dans sw.js : elles ne doivent dépendre de rien d'extérieur.

// Nom changé quand la règle change : l'ancien cache, qui a pu garder des brouillons, n'est plus relu
export const API_CACHE_NAME = 'api-public-v2'

export const apiRuntimeCaching = {
  // Jamais les données personnelles
  urlPattern: ({ request, url }) => request.method === 'GET'
    && url.pathname.startsWith('/api/')
    && !/^\/api\/(admin|notes|me-favorites|page-visits|customization|proposition-ia|users)/.test(url.pathname),
  // Réseau d'abord, sans délai d'abandon : le cache ne sert que si le réseau est coupé.
  // Avec un délai, une réponse lente laissait voir une ancienne version, ou la liste d'un autre compte.
  handler: 'NetworkFirst',
  options: {
    cacheName: API_CACHE_NAME,
    expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 * 7 },
    plugins: [{
      // Le serveur marque les réponses d'un administrateur (brouillons) : elles ne sont jamais gardées
      // (src/EventListener/ApiOfflineCacheListener.php)
      cacheWillUpdate: async ({ response }) =>
        response && response.status === 200 && !response.headers.has('X-Offline-Cache') ? response : null
    }]
  }
}
