# 📊 Système de tracking des visites de pages

## 📝 Description

Ce système permet de **tracker automatiquement les visites des utilisateurs** sur l'application SPA. Il enregistre :
- ✅ L'URL de la page visitée
- ✅ Le titre de la page
- ✅ La durée passée sur chaque page (en secondes)
- ✅ L'utilisateur qui a visité la page
- ✅ L'adresse IP et le navigateur (User-Agent)
- ✅ La date et l'heure de la visite

Le système maintient automatiquement un **maximum de 200 visites** en supprimant les plus anciennes.

---

## 🏗️ Architecture

### Backend (Symfony)

#### 1. **Entité `UserPageVisit`**
📁 `src/Entity/UserPageVisit.php`

Stocke les informations de chaque visite :
```php
- id (int)
- user (User) - Relation Many-to-One
- pageUrl (string) - URL visitée
- pageTitle (string) - Titre de la page
- visitedAt (DateTime) - Date/heure de visite
- timeSpent (int) - Durée en secondes
- ipAddress (string) - Adresse IP
- userAgent (string) - Navigateur
```

#### 2. **API Controller**
📁 `src/Controller/Api/UserPageVisitController.php`

**Endpoints disponibles :**

| Route | Méthode | Description | Auth |
|-------|---------|-------------|------|
| `/api/page-visits` | POST | Enregistrer une visite | ✅ ROLE_USER |
| `/api/page-visits/my-history` | GET | Historique de l'utilisateur | ✅ ROLE_USER |
| `/api/page-visits/my-stats` | GET | Statistiques (pages les + visitées) | ✅ ROLE_USER |

**Exemple de requête POST :**
```json
{
  "pageUrl": "/pages/symfony",
  "pageTitle": "Introduction à Symfony",
  "timeSpent": 45
}
```

**Filtrage automatique :** Les URLs contenant `/admin` ou `/api/` sont automatiquement ignorées.

#### 3. **CRUD EasyAdmin**
📁 `src/Controller/Admin/UserPageVisitCrudController.php`

Interface d'administration accessible via `/admin` → "Visites"

**Fonctionnalités :**
- ✅ Visualisation de toutes les visites
- ✅ Filtres par utilisateur, URL, date
- ✅ Tri par date décroissante
- ✅ Affichage formaté de la durée (ex: "2m 30s")
- ✅ Suppression manuelle
- ❌ Pas de création/édition (automatique uniquement)

#### 4. **Nettoyage automatique**
📁 `src/EventListener/UserPageVisitListener.php`

**Listener Doctrine** qui se déclenche après chaque insertion (`postPersist`) :
- Vérifie le nombre total de visites
- Si > 200, supprime automatiquement les plus anciennes
- Transparent pour l'utilisateur

**Configuration dans `config/services.yaml` :**
```yaml
App\EventListener\UserPageVisitListener:
    tags:
        - { name: doctrine.event_listener, event: postPersist }
```

#### 5. **Commande de nettoyage manuel**
📁 `src/Command/CleanupPageVisitsCommand.php`

**Utilisation :**
```bash
# Voir ce qui serait supprimé (sans supprimer)
php bin/console app:cleanup-page-visits --dry-run

# Supprimer réellement avec la limite par défaut (200)
php bin/console app:cleanup-page-visits

# Personnaliser la limite (ex: garder 100 visites)
php bin/console app:cleanup-page-visits --limit=100

# Aide
php bin/console app:cleanup-page-visits --help
```

#### 6. **Sécurité**
📁 `config/packages/security.yaml`

```yaml
firewalls:
    api_page_visits:
        pattern: ^/api/page-visits
        stateless: false
        provider: app_user_provider
        context: main

access_control:
    - { path: ^/api/page-visits, roles: ROLE_USER }
```

**Important :** L'API nécessite une authentification Symfony (session).

---

### Frontend (Vue.js)

#### 1. **Composable de tracking**
📁 `front/src/composables/usePageTracking.js`

**Fonctionnement :**
- S'initialise automatiquement au montage de l'application
- Écoute les changements de route (`router.afterEach`)
- Calcule le temps passé sur chaque page
- Envoie les données à l'API via axios
- Gère la fermeture de l'onglet avec `sendBeacon`

**Caractéristiques :**
- ⏱️ Minimum de 1 seconde pour éviter les faux positifs
- 📤 Envoi en arrière-plan (non bloquant)
- 🔇 Échec silencieux si l'utilisateur n'est pas connecté
- 📊 Logs dans la console en développement

#### 2. **Intégration dans App.vue**
📁 `front/src/App.vue`

```javascript
import { usePageTracking } from "./composables/usePageTracking.js";

// Initialiser le tracking
usePageTracking();
```

#### 3. **Configuration Axios**
📁 `front/src/main.js`

```javascript
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

**Important :** `withCredentials: true` permet d'envoyer les cookies de session.

---

## 🗄️ Base de données

### Table `appy_UserPageVisit`

```sql
CREATE TABLE appy_UserPageVisit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    page_url VARCHAR(255) NOT NULL,
    page_title VARCHAR(255) DEFAULT NULL,
    visited_at DATETIME NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    time_spent INT DEFAULT NULL,
    INDEX IDX_user_id (user_id),
    INDEX IDX_user_visited_at (user_id, visited_at),
    FOREIGN KEY (user_id) REFERENCES appy_User (id)
);
```

### Migration
📁 `migrations/Version20251010000000.php`

**Appliquer la migration :**
```bash
php bin/console doctrine:migrations:migrate
```

---

## 🎯 Comment ça marche en pratique

### Flux complet d'une visite

1. **Utilisateur navigue** vers `/pages/symfony`
2. **Vue Router** détecte le changement de route
3. **usePageTracking** calcule le temps passé sur la page précédente
4. **Envoi API** : POST `/api/page-visits` avec les données
5. **Backend** vérifie l'authentification et enregistre la visite
6. **Listener Doctrine** vérifie si total > 200
7. **Nettoyage auto** : supprime les visites les plus anciennes si nécessaire
8. **Confirmation** : Log dans la console "✅ Page visit tracked"

### Exemple de logs console (développement)

```
✅ Page visit tracked: /pages/symfony 45s
✅ Page visit tracked: /pages/vuejs 120s
ℹ️ User not authenticated, skipping page tracking
```

---

## ⚙️ Configuration et personnalisation

### 1. **Modifier la limite de visites**

#### Option A : Listener automatique
📁 `src/EventListener/UserPageVisitListener.php`

```php
private const MAX_VISITS = 200; // Changer ici
```

#### Option B : Commande manuelle
```bash
php bin/console app:cleanup-page-visits --limit=500
```

### 2. **Exclure d'autres URLs du tracking**

📁 `src/Controller/Api/UserPageVisitController.php`

```php
// Ligne ~35
if (str_contains($pageUrl, '/admin') ||
    str_contains($pageUrl, '/api/') ||
    str_contains($pageUrl, '/profile')) {  // Ajouter ici
    return new JsonResponse(['success' => true, 'ignored' => true]);
}
```

### 3. **Changer le temps minimum de tracking**

📁 `front/src/composables/usePageTracking.js`

```javascript
// Ligne ~31 et ~49
if (timeSpent > 1000) {  // Modifier ici (millisecondes)
```

### 4. **Désactiver les logs console en production**

📁 `front/src/composables/usePageTracking.js`

```javascript
const isDev = import.meta.env.DEV;

if (isDev) {
  console.log('✅ Page visit tracked:', pageUrl, `${Math.round(timeSpent / 1000)}s`);
}
```

### 5. **Ajouter des champs supplémentaires**

**Backend :**
1. Ajouter le champ dans l'entité `UserPageVisit`
2. Créer une migration : `php bin/console make:migration`
3. Appliquer : `php bin/console doctrine:migrations:migrate`
4. Modifier le contrôleur pour accepter le nouveau champ

**Frontend :**
```javascript
await axios.post('/api/page-visits', {
  pageUrl,
  pageTitle,
  timeSpent,
  customField: 'valeur' // Nouveau champ
});
```

### 6. **Planifier un nettoyage périodique (Cron)**

Ajouter dans le crontab du serveur :
```bash
# Tous les jours à 3h du matin
0 3 * * * /usr/bin/php /path/to/project/bin/console app:cleanup-page-visits
```

---

## 🐛 Débogage

### Problème : Pas de visites enregistrées

**1. Vérifier que l'utilisateur est connecté**
```javascript
// Console navigateur
console.log(window.location.href); // Doit être sur le même domaine que l'API
```

**2. Vérifier les logs console**
```
✅ Page visit tracked → OK
❌ Failed to track → Erreur côté backend
ℹ️ User not authenticated → Pas connecté
```

**3. Vérifier la configuration Symfony**
```bash
php bin/console debug:config security
```

**4. Vérifier les routes API**
```bash
php bin/console debug:router | grep page-visits
```

### Problème : Erreur 401 Unauthorized

**Vérifier la session :**
```bash
# Dans la console navigateur
document.cookie // Doit contenir PHPSESSID
```

**Vérifier security.yaml :**
```yaml
api_page_visits:
    pattern: ^/api/page-visits
    stateless: false  # Important !
    context: main
```

### Problème : Le nettoyage ne fonctionne pas

**Vérifier le listener :**
```bash
php bin/console debug:event-dispatcher doctrine.event_listener
```

**Vérifier manuellement :**
```bash
php bin/console app:cleanup-page-visits --dry-run
```

---

## 📊 Requêtes SQL utiles

### Statistiques globales
```sql
-- Nombre total de visites
SELECT COUNT(*) FROM appy_UserPageVisit;

-- Nombre de visites par utilisateur
SELECT u.username, COUNT(*) as nb_visites
FROM appy_UserPageVisit v
JOIN appy_User u ON v.user_id = u.id
GROUP BY u.username
ORDER BY nb_visites DESC;

-- Pages les plus visitées
SELECT page_title, COUNT(*) as nb_visites
FROM appy_UserPageVisit
GROUP BY page_title
ORDER BY nb_visites DESC
LIMIT 10;

-- Temps moyen par page
SELECT page_title, AVG(time_spent) as avg_seconds
FROM appy_UserPageVisit
WHERE time_spent IS NOT NULL
GROUP BY page_title
ORDER BY avg_seconds DESC;
```

### Nettoyage manuel
```sql
-- Supprimer les visites de plus de 30 jours
DELETE FROM appy_UserPageVisit
WHERE visited_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Garder seulement les 100 dernières visites
DELETE FROM appy_UserPageVisit
WHERE id NOT IN (
    SELECT id FROM (
        SELECT id FROM appy_UserPageVisit
        ORDER BY visited_at DESC
        LIMIT 100
    ) as temp
);
```

---

## 🚀 Déploiement en production

### Checklist

- [ ] **1. Migrations**
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

- [ ] **2. Build frontend**
```bash
cd front/
npm run build
```

- [ ] **3. Clear cache**
```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

- [ ] **4. Permissions**
```bash
chmod -R 775 var/cache var/log
chown -R www-data:www-data var/cache var/log
```

- [ ] **5. Configuration CORS (si nécessaire)**
```yaml
# config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
        allow_credentials: true
```

- [ ] **6. Vérifier les variables d'environnement**
```bash
# .env.prod
APP_ENV=prod
APP_DEBUG=0
```

### Configuration serveur web (Nginx)

```nginx
location /api/page-visits {
    try_files $uri /index.php$is_args$args;
}
```

---

## 📊 Page de statistiques EasyAdmin

### Accès

Allez sur `/admin` → Menu "👥 Utilisateurs" → **"Statistiques des visites"**

Ou directement: `/admin/stats/page-visits`

### Statistiques disponibles

#### 1. **Vue d'ensemble**
Deux cartes en haut de page affichant:
- 📈 **Total des visites** - Nombre total de pages visitées (limite: 200 max)
- 👥 **Utilisateurs actifs** - Nombre d'utilisateurs ayant visité au moins une page

#### 2. **📅 Visites des 7 derniers jours**
Tableau avec graphiques en barres montrant:
- Date (format: dd/mm/yyyy)
- Nombre de visites par jour
- Barre de progression visuelle (proportionnelle au maximum)

**Exemple:**
```
Date       | Visites | Graphique
-----------|---------|------------------
10/01/2025 | 45      | ████████░░ 45
11/01/2025 | 52      | ██████████ 52
12/01/2025 | 38      | ███████░░░ 38
```

#### 3. **🏆 Top 20 - Pages les plus visitées**
Tableau scrollable (600px max) affichant:
- **Rang** (#1, #2, etc.)
- **Titre de la page** (en gras) et URL (en petit)
- **Nombre de visites** (badge bleu)
- **Temps moyen** passé sur la page (format: Xm Ys ou Ys)

**Colonnes:**
| # | Page | Visites | Temps moy. |
|---|------|---------|------------|
| 1 | Introduction à Symfony<br>*/pages/symfony* | 125 | 2m 30s |

**Tri:** Par nombre de visites décroissant

#### 4. **⏱️ Top 20 - Pages avec le plus de temps total**
Tableau scrollable affichant les pages où les utilisateurs passent le plus de temps cumulé:
- **Rang**
- **Titre de la page** et URL
- **Temps total** cumulé (format: Xh Ym ou Ym)
- **Temps moyen** (en petit sous le temps total)
- **Nombre de visites** (badge gris)

**Exemple:**
```
Temps total: 5h 30m
Temps moyen: 12m 45s
Visites: 26
```

**Tri:** Par temps total décroissant

**Utilité:** Identifier les pages qui retiennent le plus l'attention des utilisateurs

#### 5. **👥 Top 10 - Utilisateurs les plus actifs**
Tableau complet montrant:
- **Rang** (#1 à #10)
- **Username** (en gras) avec ID utilisateur en dessous
- **Nombre de visites** (badge vert)
- **Temps total** passé (format: Xh Ym ou Ym)
- **Barre de progression** relative au plus actif (100% = utilisateur #1)

**Colonnes:**
| # | Utilisateur | Visites | Temps total | Activité |
|---|-------------|---------|-------------|----------|
| 1 | **cundo2**<br>*ID: 5* | 156 | 3h 25m | ██████████ 100% |
| 2 | **john_doe**<br>*ID: 12* | 98 | 2h 10m | ██████░░░░ 62% |

### Fichiers concernés

| Fichier | Description |
|---------|-------------|
| `src/Controller/Admin/PageVisitStatsController.php` | Contrôleur principal |
| `templates/admin/page_visit_stats.html.twig` | Vue Twig avec tous les tableaux |
| `src/Repository/UserPageVisitRepository.php` | Méthodes de statistiques (lignes 40-100) |
| `src/Controller/Admin/DashboardController.php:83` | Lien menu EasyAdmin |

### Méthodes Repository utilisées

```php
// Pages les plus visitées (tous utilisateurs)
getGlobalMostVisitedPages(20)
→ Retourne: pageUrl, pageTitle, visitCount, avgTimeSpent

// Utilisateurs les plus actifs
getMostActiveUsers(10)
→ Retourne: userId, username, visitCount, totalTime

// Visites par jour (7 derniers jours)
getVisitsPerDay(7)
→ Retourne: date, visitCount

// Pages avec le plus de temps passé
getPagesWithMostTime(20)
→ Retourne: pageUrl, pageTitle, avgTimeSpent, totalTimeSpent, visitCount
```

### Design et UX

**Couleurs et styles:**
- 🔵 Bleu (primary) - Pages les plus visitées
- 🟡 Jaune (warning) - Temps passé
- 🟢 Vert (success) - Utilisateurs actifs
- ℹ️ Info (bleu clair) - Visites par jour

**Fonctionnalités:**
- ✅ Tableaux avec header sticky (reste visible au scroll)
- ✅ Responsive (2 colonnes → 1 colonne sur mobile)
- ✅ Badges colorés pour les métriques importantes
- ✅ Barres de progression pour visualiser les proportions
- ✅ Formatage intelligent des durées (heures, minutes, secondes)
- ✅ Texte tronqué avec tooltips pour les longs titres

**Encart informatif en bas:**
```
ℹ️ Informations
- Temps moyen : Durée moyenne passée sur une page
- Temps total : Somme de toutes les durées sur une page
- Les données sont limitées aux 200 visites les plus récentes
- Les pages admin et API ne sont pas trackées
```

### Personnalisation

#### Modifier les limites d'affichage

**Top 20 → Top 50 pages:**
```php
// PageVisitStatsController.php ligne 19
$mostVisitedPages = $this->visitRepository->getGlobalMostVisitedPages(50);
```

**Top 10 → Top 20 utilisateurs:**
```php
// PageVisitStatsController.php ligne 20
$mostActiveUsers = $this->visitRepository->getMostActiveUsers(20);
```

**7 jours → 30 jours:**
```php
// PageVisitStatsController.php ligne 21
$visitsPerDay = $this->visitRepository->getVisitsPerDay(30);
```

#### Ajouter de nouvelles statistiques

**1. Créer la méthode dans le Repository:**
```php
// UserPageVisitRepository.php
public function getMyCustomStat()
{
    return $this->createQueryBuilder('v')
        ->select('...')
        ->getQuery()
        ->getResult();
}
```

**2. Appeler dans le contrôleur:**
```php
// PageVisitStatsController.php
$myCustomStat = $this->visitRepository->getMyCustomStat();

return $this->render('admin/page_visit_stats.html.twig', [
    // ...
    'myCustomStat' => $myCustomStat,
]);
```

**3. Afficher dans le template:**
```twig
{# page_visit_stats.html.twig #}
<div class="card">
    <div class="card-header">
        <h4>Ma nouvelle statistique</h4>
    </div>
    <div class="card-body">
        {% for item in myCustomStat %}
            {{ item.field }}
        {% endfor %}
    </div>
</div>
```

#### Ajouter des graphiques (Chart.js)

**1. Installer Chart.js:**
```bash
npm install chart.js
```

**2. Ajouter dans le template:**
```twig
<canvas id="myChart" width="400" height="200"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('myChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [{% for stat in visitsPerDay %}'{{ stat.date|date("d/m") }}',{% endfor %}],
        datasets: [{
            label: 'Visites',
            data: [{% for stat in visitsPerDay %}{{ stat.visitCount }},{% endfor %}],
            backgroundColor: 'rgba(54, 162, 235, 0.5)'
        }]
    }
});
</script>
```

### Export des données

Pour exporter les statistiques en CSV/Excel, ajoutez ces méthodes:

```php
// PageVisitStatsController.php
#[Route('/admin/stats/page-visits/export', name: 'admin_stats_export')]
public function export(): Response
{
    $mostVisitedPages = $this->visitRepository->getGlobalMostVisitedPages(100);

    $csv = fopen('php://temp', 'w');
    fputcsv($csv, ['Rang', 'Page', 'URL', 'Visites', 'Temps moyen (s)']);

    foreach ($mostVisitedPages as $index => $page) {
        fputcsv($csv, [
            $index + 1,
            $page['pageTitle'] ?? 'Sans titre',
            $page['pageUrl'],
            $page['visitCount'],
            $page['avgTimeSpent'] ?? 0
        ]);
    }

    rewind($csv);
    $output = stream_get_contents($csv);
    fclose($csv);

    return new Response($output, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="statistiques-visites.csv"'
    ]);
}
```

**Ajouter le bouton dans le template:**
```twig
<a href="{{ path('admin_stats_export') }}" class="btn btn-success mb-3">
    <i class="fa fa-download"></i> Exporter en CSV
</a>
```

### Dépannage

**Problème: "No route found for GET /admin/stats/page-visits"**
```bash
# Vérifier que la route existe
php bin/console debug:router admin_stats_page_visits

# Clear cache
php bin/console cache:clear
```

**Problème: Page blanche ou erreur 500**
```bash
# Vérifier les logs
tail -f var/log/dev.log

# Vérifier que le template existe
ls -la templates/admin/page_visit_stats.html.twig
```

**Problème: "Class not found" ou "Service not found"**
```bash
# Rebuild le container si vous utilisez Symfony
php bin/console cache:clear
composer dump-autoload
```

**Problème: Aucune donnée affichée**
- Vérifiez qu'il y a des visites en base: `SELECT COUNT(*) FROM appy_UserPageVisit;`
- Naviguez sur quelques pages du SPA pour générer des données
- Vérifiez que le tracking fonctionne (console navigateur)

## 📈 Évolutions possibles

### Idées d'améliorations

1. **Dashboard statistiques avancé**
   - Graphiques interactifs (Chart.js, ApexCharts)
   - Heatmap des pages les plus visitées
   - Timeline de navigation
   - Graphiques en temps réel

2. **Analyse comportementale**
   - Parcours utilisateur (séquence de pages)
   - Taux de rebond par page
   - Pages de sortie

3. **Export des données**
   - Export CSV/Excel
   - Intégration Google Analytics
   - Webhooks vers des outils externes

4. **Optimisation performances**
   - Queue asynchrone (Messenger) pour l'enregistrement
   - Cache Redis pour les statistiques
   - Agrégation des données par jour

5. **Notifications**
   - Alertes si une page a peu de visites
   - Rapport hebdomadaire par email
   - Détection d'anomalies

---

## 📚 Ressources

### Documentation Symfony
- [Security](https://symfony.com/doc/current/security.html)
- [Doctrine Events](https://symfony.com/doc/current/doctrine/events.html)
- [Console Commands](https://symfony.com/doc/current/console.html)

### Documentation Vue.js
- [Composables](https://vuejs.org/guide/reusability/composables.html)
- [Vue Router](https://router.vuejs.org/)
- [Axios](https://axios-http.com/docs/intro)

### Outils
- [EasyAdmin](https://symfony.com/bundles/EasyAdminBundle/current/index.html)
- [Doctrine Migrations](https://www.doctrine-project.org/projects/migrations.html)

---

## 📝 Licence & Support

**Auteur :** Votre équipe de développement
**Date de création :** 2025-01-10
**Dernière mise à jour :** 2025-01-10
**Version :** 1.0.0

Pour toute question ou suggestion : [Créer une issue GitHub]

---

## ✅ Résumé rapide

| Composant | Fichier | Action |
|-----------|---------|--------|
| **Entité** | `src/Entity/UserPageVisit.php` | Modèle de données |
| **API** | `src/Controller/Api/UserPageVisitController.php` | Endpoints REST |
| **Admin** | `src/Controller/Admin/UserPageVisitCrudController.php` | Interface admin |
| **Listener** | `src/EventListener/UserPageVisitListener.php` | Nettoyage auto (200 max) |
| **Command** | `src/Command/CleanupPageVisitsCommand.php` | Nettoyage manuel |
| **Frontend** | `front/src/composables/usePageTracking.js` | Tracking automatique |
| **Migration** | `migrations/Version20251010000000.php` | Création table |
| **Config** | `config/packages/security.yaml` | Sécurité API |

**En un mot :** Système **100% automatique** qui track et nettoie les visites sans intervention manuelle ! 🎉
