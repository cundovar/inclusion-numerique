# Configuration des Assets

Ce projet utilise deux systèmes de build séparés pour éviter les conflits :

## 🔧 EasyAdmin (Webpack Encore)
**Répertoire :** Racine du projet  
**Build output :** `public/build/`  
**Usage :** Assets minimaux pour l'administration Symfony

### Commandes :
```bash
# Development
npm run dev

# Watch mode  
npm run watch

# Production
npm run build
```

## ⚡ Front-end Vue.js (Vite)
**Répertoire :** `front/`  
**Build output :** `public/build/assets/`  
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
Pour builder les deux systèmes en une fois :

```bash
./build-assets.sh
```

## 📁 Structure des Assets
```
public/build/
├── assets/           # Vue.js (Vite)
│   ├── main-*.js
│   ├── main-*.css
│   └── fonts/
├── easyadmin.*.js    # EasyAdmin (Webpack)
├── manifest.json     # Webpack manifest
└── .vite/           # Vite metadata
    ├── manifest.json
    └── entrypoints.json
```

## ⚠️ Important
- **Ne jamais** mélanger les configurations Webpack et Vite
- EasyAdmin utilise uniquement le minimum requis via Webpack
- Vue.js bénéficie des performances optimales de Vite
- Les deux systèmes coexistent sans conflit

## 🐳 Docker
Les builds sont également compatibles avec l'environnement Docker :

```bash
# Build EasyAdmin dans le container principal
npm run build

# Build Vue.js dans le container front
docker compose exec front npm run build
```