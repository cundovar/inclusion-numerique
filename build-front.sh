#!/bin/bash

# Script pour builder le frontend Vue.js avec Docker

echo "🚀 Building Vue.js frontend with Docker..."

# Arrêter et supprimer le container s'il existe
docker-compose down front-build 2>/dev/null || true
docker-compose rm -f front-build 2>/dev/null || true

# Créer le dossier de build s'il n'existe pas
mkdir -p public/build

# Lancer le build
docker-compose run --rm front-build

echo "✅ Build terminé ! Les fichiers sont dans public/build/"