#!/bin/bash

# Script de build des assets pour le projet
# Gère séparément EasyAdmin (Webpack) et Front-end (Vite)

echo "🔧 Building assets for production..."

# 1. Build EasyAdmin assets avec Webpack
echo "📦 Building EasyAdmin assets with Webpack..."
npm run build

# 2. Build Front-end Vue.js avec Vite
echo "⚡ Building Vue.js front-end with Vite..."
cd front
BUILD_OUTPUT_DIR=../public/build PWA_OUTPUT_DIR=../public/spa npm run build
cd ..

# 3. Vérification des assets générés
echo "✅ Checking generated assets..."

if [ -d "public/build" ]; then
    echo "   ✓ EasyAdmin assets generated in public/build/"
    ls -la public/build/
else
    echo "   ❌ EasyAdmin assets not found!"
fi

if [ -d "public/build/assets" ]; then
    echo "   ✓ Vue.js assets generated in public/build/assets/"
    ls -la public/build/assets/ | head -5
else
    echo "   ❌ Vue.js assets not found!"
fi

echo "🎉 Assets build completed!"
echo ""
echo "📝 Note: "
echo "   - EasyAdmin uses Webpack Encore (public/build/)"
echo "   - Vue.js front-end uses Vite (public/build/assets/)"
echo "   - Both systems work independently without conflicts"
