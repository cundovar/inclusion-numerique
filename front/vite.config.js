import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import symfony from 'vite-plugin-symfony'
import { VitePWA } from 'vite-plugin-pwa'
import { resolve } from 'path'

export default defineConfig({
  base: '/build/',
  plugins: [
    vue(),
    symfony(),
    VitePWA({
      registerType: 'autoUpdate',
      manifestFilename: 'manifest.webmanifest',
      injectRegister: 'auto',
      outDir: '../public/spa', // SW et manifest ici
      manifest: {
        name: 'spa hibryde vue_symfony',
        short_name: 'spaVueSymfo',
        start_url: '/spa/',
        scope: '/spa/',
        description: 'mon truc',
        theme_color: '#ffffff',
        display: 'standalone',
        includeAssets: ['favicon.svg', 'robots.txt', 'sitemaps.xml'],
        icons: [
          {
            src: '/spa/pwa.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/spa/pwa1.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      },
      workbox: {
        globDirectory: '../public/build', // ✅ ici on pointe vers les JS/CSS
        globPatterns: ['**/*.{js,wasm,css,html,png,svg}'],
        globIgnores: ['manifest.webmanifest', 'workbox-*.js', 'sw.js']
      },
      screenshots: [
        {
          src: '../public/spa/screenshot.png',
          type: 'image/png',
          sizes: '720x1280',
          form_factor: 'wide'
        }
      ]
    })
  ],
  server: {
    host: true,
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://nginx',
        changeOrigin: true,
        secure: false
      }
    },
    strictPort: true,
    watch: {
      usePolling: true
    }
  },
  build: {
    outDir: '../public/build', // ✅ JS/CSS ici
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'src/main.js')
      }
    }
  }
})
