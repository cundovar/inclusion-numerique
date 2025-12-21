#!/bin/bash

# Script de sauvegarde de la base de données
# Usage: ./backup-db.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="./backups"
BACKUP_FILE="$BACKUP_DIR/db_backup_$DATE.sql"

echo "🔄 Sauvegarde de la base de données..."

# Créer le dossier si nécessaire
mkdir -p $BACKUP_DIR

# Sauvegarder la base
docker compose exec -T db mysqldump -uroot -proot courto > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Sauvegarde réussie: $BACKUP_FILE"
    echo "📊 Taille: $(du -h "$BACKUP_FILE" | cut -f1)"

    # Garder seulement les 10 dernières sauvegardes
    ls -t $BACKUP_DIR/db_backup_*.sql | tail -n +11 | xargs -r rm
    echo "🧹 Anciennes sauvegardes nettoyées (garde les 10 dernières)"
else
    echo "❌ Erreur lors de la sauvegarde"
    exit 1
fi
