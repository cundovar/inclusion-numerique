#!/usr/bin/env bash
# Bascule des images d'inclusion-numerique : du lien vers les images de mediateur à un dossier propre.
# À lancer sur le serveur OVH. Procédure complète : docs/deploiement/index.md.
#
#   inventory  lecture seule : fichiers, volume, liens, références en base, URL absolues
#   prepare    copie (ou recopie) les images dans var/uploads-copie ; ne touche ni au lien ni à mediateur
#   verify     compare la copie à la source : nombre, tailles, empreintes
#   switch     après le gel des images : dernière copie, contrôle, remplacement du lien par le dossier
#   rollback   remet le lien ; garde le dossier d'inclusion et signale les images créées depuis la bascule
#
# Seul `switch` et `rollback` changent ce que le site sert. Aucune commande n'écrit dans mediateur ni en base.
set -euo pipefail

SITE="${SITE_DIR:-$HOME/inclusion-numerique}"
SOURCE="${SOURCE_DIR:-$HOME/mediateur/public/uploads}"
LIVE="$SITE/public/uploads"
# Sous var/ : même disque (remplacement atomique), hors du web, et épargné par le rsync --delete du déploiement
COPY="$SITE/var/uploads-copie"
PREVIOUS="$SITE/var/uploads-avant-retour"

fail() { echo "ERREUR : $*" >&2; exit 1; }

require_source() {
    [ -d "$SOURCE" ] && [ ! -L "$SOURCE" ] || fail "source absente ou lien : $SOURCE"
}

# Liste triée « taille empreinte chemin » d'un dossier, sans suivre les liens
fingerprint() {
    (cd "$1" && find . -type f -printf '%s %p\n' | sort -k2 | while read -r size path; do
        printf '%s %s %s\n' "$size" "$(sha256sum "$path" | cut -d' ' -f1)" "$path"
    done)
}

sql() { (cd "$SITE" && php bin/console dbal:run-sql --env=prod --no-debug "$1"); }

inventory() {
    require_source
    echo "--- Dossier actif"
    if [ -L "$LIVE" ]; then echo "lien -> $(readlink "$LIVE")"; elif [ -d "$LIVE" ]; then echo "dossier propre"; else echo "absent"; fi
    echo "--- Source : $SOURCE"
    echo "fichiers : $(find "$SOURCE" -type f | wc -l)"
    echo "liens    : $(find "$SOURCE" -type l | wc -l)"
    du -sh "$SOURCE"
    find "$SOURCE" -mindepth 1 -type d -printf 'dossier : %P\n'
    echo "--- Espace disponible"
    df -h "$SITE" | tail -1
    echo "--- Base (lecture seule)"
    sql "SELECT COUNT(*) AS medias, COUNT(filename) AS avec_fichier, COUNT(DISTINCT filename) AS fichiers_distincts FROM course_media"
    echo "--- Fichiers référencés en base et absents du disque"
    local listing missing=0
    listing="$(sql "SELECT filename FROM course_media WHERE filename IS NOT NULL" | grep -E '^ *[^ ]+\.(png|jpe?g|webp) *$' | tr -d ' ' || true)"
    while read -r name; do
        [ -n "$name" ] || continue
        [ -f "$SOURCE/course-media/$name" ] || { echo "manquant : $name"; missing=$((missing + 1)); }
    done <<<"$listing"
    echo "manquants : $missing"
    echo "--- URL absolues vers /uploads en base (à traiter avant la bascule si le total n'est pas nul)"
    # Colonnes texte de la base courante, sans afficher leur contenu : on ne compte que les correspondances
    local columns table column union=""
    columns="$(sql "SELECT CONCAT(table_name, '.', column_name) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND data_type IN ('text','mediumtext','longtext','varchar') AND table_name NOT LIKE 'oauth\_%' AND table_name NOT LIKE '%password%' AND table_name NOT LIKE '%User%'" | grep -E '^ *[A-Za-z0-9_]+\.[A-Za-z0-9_]+ *$' | tr -d ' ' || true)"
    while IFS=. read -r table column; do
        [ -n "$table" ] || continue
        union="${union:+$union UNION ALL }SELECT '$table.$column' AS colonne, COUNT(*) AS total FROM \`$table\` WHERE \`$column\` REGEXP 'https?://[^\"'' ]*/uploads/'"
    done <<<"$columns"
    [ -z "$union" ] || sql "SELECT * FROM ($union) AS t WHERE total > 0"
    echo "--- Fin (rien n'a été modifié)"
}

prepare() {
    require_source
    mkdir -p "$COPY"
    # --safe-links : un lien sortant de la source n'est ni suivi ni copié ; inventory compte les liens
    rsync -rlptD --delete --safe-links "$SOURCE/" "$COPY/"
    echo "Copie prête : $(find "$COPY" -type f | wc -l) fichiers dans $COPY"
}

verify() {
    require_source
    [ -d "$COPY" ] || fail "pas de copie : lancer prepare"
    local src_list copy_list
    src_list="$(fingerprint "$SOURCE")"
    copy_list="$(fingerprint "$COPY")"
    if [ "$src_list" != "$copy_list" ]; then
        diff <(echo "$src_list") <(echo "$copy_list") | head -40 >&2 || true
        fail "la copie diffère de la source"
    fi
    if find "$COPY" ! -readable | grep -q .; then fail "fichiers illisibles dans la copie"; fi
    echo "Copie conforme : $(echo "$src_list" | grep -c . ) fichiers, $(du -sh "$COPY" | cut -f1)"
}

switch_uploads() {
    [ -L "$LIVE" ] || fail "$LIVE n'est pas un lien : bascule déjà faite ?"
    [ "$(readlink -f "$LIVE")" = "$(readlink -f "$SOURCE")" ] || fail "$LIVE ne pointe pas vers $SOURCE"
    prepare
    verify
    rm "$LIVE"
    if ! mv "$COPY" "$LIVE"; then
        ln -s "$SOURCE" "$LIVE"
        fail "déplacement impossible : lien remis"
    fi
    echo "Bascule faite : $LIVE est un dossier propre. Retour arrière : $0 rollback"
}

rollback() {
    [ -d "$LIVE" ] && [ ! -L "$LIVE" ] || fail "$LIVE n'est pas un dossier : rien à annuler"
    require_source
    [ ! -e "$PREVIOUS" ] || fail "$PREVIOUS existe déjà : l'examiner avant de recommencer"
    echo "Images présentes ici et absentes de mediateur (à recopier à la main si besoin) :"
    (cd "$LIVE" && find . -type f) | while read -r path; do
        [ -f "$SOURCE/$path" ] || echo "  $path"
    done
    mv "$LIVE" "$PREVIOUS"
    ln -s "$SOURCE" "$LIVE"
    echo "Lien remis. Dossier d'inclusion conservé dans $PREVIOUS"
}

case "${1:-}" in
    inventory) inventory ;;
    prepare) prepare ;;
    verify) verify ;;
    switch) switch_uploads ;;
    rollback) rollback ;;
    *) echo "Usage : $0 {inventory|prepare|verify|switch|rollback}" >&2; exit 2 ;;
esac
