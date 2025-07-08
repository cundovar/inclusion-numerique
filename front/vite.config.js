
// On importe la fonction qui permet de définir la config de Vite
import { defineConfig } from 'vite'

// On importe le plugin Vite pour Vue.js
import vue from '@vitejs/plugin-vue'

// On importe un plugin qui facilite l'intégration avec Symfony
import symfony from 'vite-plugin-symfony'

// On importe le plugin qui gère le mode PWA (Progressive Web App)
import { VitePWA } from 'vite-plugin-pwa'

// On importe la fonction pour gérer des chemins (path) facilement
import { resolve } from 'path'

// On exporte la configuration principale de Vite
export default defineConfig({
  // Définit le chemin de base pour les fichiers générés (ici, /build/)
  base: '/build/',

  // Liste des plugins utilisés par Vite
  plugins: [
    vue(),        // Active le support de Vue.js dans Vite
    symfony(),    // Active l'intégration spéciale avec Symfony
    VitePWA({     // Configure le plugin PWA
      registerType: 'autoUpdate',         // Le service worker se met à jour automatiquement
      manifestFilename: 'manifest.webmanifest', // Nom du fichier manifest généré
      injectRegister: 'auto',             // Injection automatique du registre du service worker
      outDir: '../public/spa',            // Où mettre le service worker et le manifest
      manifest: {                         // Contenu du manifest PWA
        name: 'spa hibryde vue_symfony',  // Nom complet de l'application
        short_name: 'spaVueSymfo',        // Nom court (pour l'écran d'accueil)
        start_url: '/spa/',               // URL de démarrage quand l'app est lancée
        scope: '/spa/',                   // Limite les pages où le PWA fonctionne
        description: 'mon truc',          // Description de l'app
        theme_color: '#ffffff',           // Couleur du thème (barre de statut, etc)
        display: 'standalone',            // Affichage comme une vraie app (sans barre d’adresse)
        includeAssets: ['favicon.svg', 'robots.txt', 'sitemaps.xml'], // Fichiers inclus dans la PWA
        icons: [                          // Icônes de l'application
          {
            src: '/spa/pwa.png',          // Chemin de l'icône 192x192
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/spa/pwa1.png',         // Chemin de l'icône 512x512
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      },
      workbox: {                          // Configuration de Workbox pour gérer le cache
        globDirectory: '../public/build', // Où chercher les fichiers à mettre en cache
        globPatterns: ['**/*.{js,wasm,css,html,png,svg}'], // Quels types de fichiers à inclure
        globIgnores: ['manifest.webmanifest', 'workbox-*.js', 'sw.js'] // Quels fichiers ignorer
      },
      screenshots: [                      // Capture d'écran de l'app (pour le store, par ex)
        {
          src: '../public/spa/screenshot.png',
          type: 'image/png',
          sizes: '720x1280',
          form_factor: 'wide'
        }
      ]
    })
  ],

  // Configuration du serveur de développement Vite
  // Le proxy /api redirige vers http://nginx
  // ⚠ Ça c’est seulement utilisé par Vite Dev Server pendant le développement → ça n’a aucun effet en production
  // ⚠ En prod, ton NGINX ne voit pas ce proxy config, c’est ton serveur web qui sert Symfony et tes assets directement.
  server: {
    host: true,            // Rend le serveur accessible depuis le réseau local
    port: 5173,            // Port utilisé par le serveur
    proxy: {               // Déclare un proxy pour rediriger les appels API
      '/api': {
        target: 'http://nginx', // Redirige les requêtes /api vers nginx
        changeOrigin: true,     // Modifie l'origine pour correspondre au target
        secure: false           // Autorise les connexions non sécurisées (HTTP)
      }
    },
    strictPort: true,       // Si le port est déjà pris, ne pas essayer un autre
    watch: {
      usePolling: true      // Active le polling (utile dans certains environnements, ex : Docker)
    }
  },

  // Configuration de la build (production)
  build: {
    manifest: 'manifest.json',      // Génère un fichier manifest.json (liste des assets générés)
    outDir: '../public/build',      // Où mettre les fichiers générés JS/CSS (dossier de sortie)
    emptyOutDir: true,              // Vide le dossier avant de générer
    rollupOptions: {                // Options supplémentaires pour le bundler Rollup
      input: {
        main: resolve(__dirname, 'src/main.js') // Point d'entrée principal de l'application
      }
    }
  }
})
