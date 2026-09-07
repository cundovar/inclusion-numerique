# Configuration des Assets

Ce projet utilise un seul système de build : **Vite**, pour l'application Vue.js.

## ⚡ Front-end Vue.js (Vite)
**Répertoire :** `front/`  
**Build output :** `public/build/assets/`  
**PWA output :** `public/spa/`  
**Usage :** Application Vue.js principale

### Commandes :
```bash
cd front/

# Development
npm run dev

# Production
npm run build
```

## 🚀 Build Complet
Pour builder le front-end en une fois :

```bash
./build-assets.sh
```

Ou directement :

```bash
cd front && BUILD_OUTPUT_DIR=../public/build PWA_OUTPUT_DIR=../public/spa npm run build
```

## 📁 Structure des Assets
```
public/build/
├── assets/           # Vue.js (Vite)
│   ├── main-*.js
│   ├── main-*.css
│   └── fonts/
└── .vite/            # Vite metadata
    ├── manifest.json
    └── entrypoints.json

public/spa/            # PWA (service worker + manifest)
├── sw.js
├── workbox-*.js
└── manifest.webmanifest
```

## 🐳 Docker
Le build est également compatible avec l'environnement Docker :

```bash
# Build Vue.js dans le container front-build
docker compose run --rm front-build
```
