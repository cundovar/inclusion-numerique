#!/bin/bash

# Script de restauration de la base de données
# Usage: ./restore-db.sh [fichier_backup.sql]

if [ -z "$1" ]; then
    echo "❌ Usage: ./restore-db.sh <fichier_backup.sql>"
    echo ""
    echo "Sauvegardes disponibles:"
    ls -lth backups/db_backup_*.sql 2>/dev/null | head -10
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Fichier non trouvé: $BACKUP_FILE"
    exit 1
fi

echo "⚠️  Cette opération va REMPLACER toutes les données actuelles!"
echo "Fichier: $BACKUP_FILE"
read -p "Continuer? (y/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Annulé."
    exit 1
fi

echo "🔄 Restauration en cours..."

# Restaurer la base
docker compose exec -T db mysql -uroot -proot courto < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Restauration réussie!"
else
    echo "❌ Erreur lors de la restauration"
    exit 1
fi
