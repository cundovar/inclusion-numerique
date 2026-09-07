#!/bin/bash

# Script de build des assets pour le projet
# Gère uniquement le Front-end (Vite)

echo "🔧 Building assets for production..."

# Build Front-end Vue.js avec Vite
echo "⚡ Building Vue.js front-end with Vite..."
cd front
BUILD_OUTPUT_DIR=../public/build PWA_OUTPUT_DIR=../public/spa npm run build
cd ..

# Vérification des assets générés
echo "✅ Checking generated assets..."

if [ -d "public/build/assets" ]; then
    echo "   ✓ Vue.js assets generated in public/build/assets/"
    ls -la public/build/assets/ | head -5
else
    echo "   ❌ Vue.js assets not found!"
fi

echo "🎉 Assets build completed!"
echo ""
echo "📝 Note: "
echo "   - Vue.js front-end uses Vite (public/build/assets/)"
