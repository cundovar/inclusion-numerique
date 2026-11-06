// Règle du service worker pour les images des captures, importée par vite.config.js.
// Workbox recopie les fonctions telles quelles dans sw.js : elles ne doivent dépendre de rien d'extérieur.

// Vidé à la déconnexion comme les réponses de l'API (utils/fetchDataPwa.js) : une capture peut
// illustrer un cours réservé à la personne qui était connectée
export const COURSE_MEDIA_CACHE = 'captures-images'

export const courseMediaCaching = {
  urlPattern: ({ request, url }) => request.method === 'GET' && url.pathname.startsWith('/uploads/course-media/'),
  // Une capture remplacée reçoit un nouveau nom de fichier : la copie gardée ne devient jamais fausse
  handler: 'CacheFirst',
  options: {
    cacheName: COURSE_MEDIA_CACHE,
    expiration: { maxEntries: 500, maxAgeSeconds: 60 * 60 * 24 * 90 },
    cacheableResponse: { statuses: [200] }
  }
}
