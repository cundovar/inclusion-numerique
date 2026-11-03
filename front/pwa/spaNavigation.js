// Règle du service worker pour l'ouverture des pages de la SPA, importée par vite.config.js.
// Workbox recopie les fonctions telles quelles dans sw.js : elles ne doivent dépendre de rien d'extérieur.

export const SPA_PAGES_CACHE = 'spa-pages'

// Page de la SPA préchargée à l'installation (voir pwa/precache.js). Adresse distincte de /spa/ :
// le préchargement sert toujours sa copie, alors qu'en ligne /spa/ doit venir du serveur.
export const SPA_SHELL_URL = '/spa/?hors-ligne=1'

export const spaNavigationCaching = {
  urlPattern: ({ request, url }) => request.mode === 'navigate' && url.pathname.startsWith('/spa/'),
  // Réseau d'abord : en ligne, la page vient toujours du serveur (titre, jeton de déconnexion à jour)
  handler: 'NetworkFirst',
  options: {
    cacheName: SPA_PAGES_CACHE,
    networkTimeoutSeconds: 3,
    expiration: { maxEntries: 50 },
    plugins: [{
      // Hors ligne, page jamais ouverte : le HTML est le même pour toutes les adresses /spa/*,
      // on sert une autre copie et le routeur Vue affiche la bonne vue.
      handlerDidError: async () => {
        // D'abord la page préchargée (même build que les scripts préchargés), rangée par Workbox
        // sous une clé suffixée par sa révision
        for (const name of await caches.keys()) {
          if (!name.startsWith('workbox-precache')) continue
          const cache = await caches.open(name)
          const cle = (await cache.keys()).find((request) => request.url.includes('/spa/?hors-ligne=1'))
          if (cle) return cache.match(cle)
        }

        // Sinon une page déjà ouverte en ligne
        const pages = await caches.open('spa-pages')
        const [gardee] = await pages.keys()
        return gardee ? pages.match(gardee) : Response.error()
      }
    }]
  }
}
