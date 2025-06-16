import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'


export default defineConfig({
  plugins: [
  
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'Mon Application Vue SPA',
        short_name: 'VueSPA',
        start_url: '/',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#42b983',
        icons: [
          {
            src: '/pwa-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    })
  ],
  build: {
    outDir: '../public/build',
    emptyOutDir: true,
  },
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    watch: {
      usePolling: true,
    },
    proxy: {
      '/api': {
        target: 'http://nginx:80',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})


// Avec ça, tout ce qui commence par /api sera redirigé vers Symfony.
// Plus besoin de gérer CORS manuellement.

// Crée un service-worker.js automatique et mets les icônes pwa-192x192.png 
// et pwa-512x512.png dans public/.



