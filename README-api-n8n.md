# API Documentation pour n8n

Ce document détaille tous les endpoints API disponibles dans l'application Symfony pour une intégration avec n8n.

## Base URL
```
http://votre-domaine.com
```

## Authentification

### Routes publiques (lecture seule)
Les endpoints sous `/api/` (pages, menus, favoris) utilisent l'authentification par session Symfony.

### Routes Admin API (CRUD)
**Les routes sous `/api/admin/` nécessitent une authentification par API Key.**

**Configuration requise :**
1. Copiez `.env.local.example` vers `.env.local`
2. Définissez votre clé API :
```bash
# Dans .env.local
N8N_API_KEY=your_secure_64_character_api_key_here
API_ADMIN_USER_EMAIL=admin@yourdomain.com
```

**Utilisation dans n8n :**
Ajoutez le header suivant à toutes vos requêtes vers `/api/admin/` :
```
X-API-KEY: your_secure_64_character_api_key_here
```

**Exemple de requête curl :**
```bash
curl -X GET "http://votre-domaine.com/api/admin/pages" \
  -H "X-API-KEY: your_secure_64_character_api_key_here" \
  -H "Content-Type: application/json"
```

---

## ✅ NOUVEAU - Routes CRUD Admin disponibles

**Les contrôleurs CRUD Admin ont été créés et sont maintenant disponibles :**
- CRUD complet pour les Pages (`/api/admin/pages`)
- CRUD complet pour les Contenus de cours (`/api/admin/page-contents`) 
- CRUD complet pour les Catégories (`/api/admin/categories`)

**Ces routes permettent maintenant :**
- ✅ Automatiser la création de pages/cours via n8n
- ✅ Modifier le contenu existant programmatiquement
- ✅ Supprimer des éléments via API
- ✅ Gestion complète des relations entre entités

---

## 1. API Pages (Lecture seule)

### 1.1 Lister toutes les pages
**Endpoint :** `GET /api/`
**Description :** Récupère la liste de toutes les pages
**Authentification :** Non requise
**Réponse :** JSON contenant toutes les pages

**Exemple de réponse :**
```json
[
    {
        "id": 1,
        "title": "Page d'accueil",
        "slug": "home",
        "content": "...",
        "createdAt": "2024-01-01T00:00:00+00:00"
    }
]
```

### 1.2 Afficher une page spécifique
**Endpoint :** `GET /api/{id}`
**Description :** Récupère les détails d'une page par son ID
**Authentification :** Non requise
**Paramètres :**
- `id` (integer, requis) : ID de la page

**Réponses :**
- **200 OK :** Page trouvée
- **404 Not Found :** Page non trouvée

**Exemple de réponse :**
```json
{
    "id": 1,
    "title": "Page d'accueil",
    "slug": "home",
    "content": "...",
    "createdAt": "2024-01-01T00:00:00+00:00"
}
```

### 1.3 Lister les menus
**Endpoint :** `GET /api/menus`
**Description :** Récupère la liste de tous les menus
**Authentification :** Non requise
**Réponse :** JSON contenant tous les menus avec le groupe de sérialisation 'menu_list'

**Exemple de réponse :**
```json
[
    {
        "id": 1,
        "name": "Menu principal",
        "slug": "main-menu",
        "items": [...]
    }
]
```

---

## 2. API Favoris

### 2.1 Lister mes favoris
**Endpoint :** `GET /api/me-favorites/list-my-favorites`
**Description :** Récupère la liste des favoris de l'utilisateur connecté
**Authentification :** Requise
**Réponses :**
- **200 OK :** Liste des favoris
- **401 Unauthorized :** Utilisateur non authentifié

**Exemple de réponse :**
```json
[
    {
        "id": 1,
        "title": "Titre de la page",
        "category": {
            "id": 1,
            "name": "Catégorie"
        },
        "page": {
            "id": 1,
            "slug": "page-slug"
        },
        "createdAt": "2024-01-01 12:00:00"
    }
]
```

### 2.2 Afficher un favori spécifique
**Endpoint :** `GET /api/me-favorites/show-my-favorite/{id}`
**Description :** Récupère les détails d'un favori par son ID
**Authentification :** Requise
**Paramètres :**
- `id` (integer, requis) : ID du favori

**Réponses :**
- **200 OK :** Favori trouvé
- **401 Unauthorized :** Utilisateur non authentifié
- **404 Not Found :** Favori non trouvé

**Exemple de réponse :**
```json
{
    "id": 1,
    "title": "Titre de la page",
    "category": {
        "id": 1,
        "name": "Catégorie"
    },
    "page": {
        "id": 1,
        "slug": "page-slug"
    },
    "createdAt": "2024-01-01 12:00:00"
}
```

### 2.3 Créer un favori
**Endpoint :** `POST /api/me-favorites/create`
**Description :** Ajoute une page aux favoris de l'utilisateur
**Authentification :** Requise
**Corps de la requête :**
```json
{
    "pageId": 1
}
```

**Réponses :**
- **201 Created :** Favori créé avec succès
- **400 Bad Request :** pageId requis
- **401 Unauthorized :** Utilisateur non authentifié
- **404 Not Found :** Page non trouvée
- **409 Conflict :** Page déjà en favoris

### 2.4 Supprimer un favori
**Endpoint :** `DELETE /api/me-favorites/{id}`
**Description :** Supprime un favori par son ID
**Authentification :** Requise
**Paramètres :**
- `id` (integer, requis) : ID du favori

**Réponses :**
- **200 OK :** Favori supprimé
- **401 Unauthorized :** Utilisateur non authentifié
- **404 Not Found :** Favori non trouvé

**Exemple de réponse :**
```json
{
    "message": "Favori supprimé avec succès"
}
```

### 2.5 Basculer un favori (toggle)
**Endpoint :** `POST /api/me-favorites/toggle/{pageId}`
**Description :** Ajoute ou retire une page des favoris
**Authentification :** Requise
**Paramètres :**
- `pageId` (integer, requis) : ID de la page

**Réponses :**
- **200 OK :** Action effectuée
- **401 Unauthorized :** Utilisateur non authentifié
- **404 Not Found :** Page non trouvée

**Exemples de réponse :**

Ajout aux favoris :
```json
{
    "action": "added",
    "message": "Ajouté aux favoris",
    "isFavorite": true,
    "favoriteId": 1
}
```

Retrait des favoris :
```json
{
    "action": "removed",
    "message": "Retiré des favoris",
    "isFavorite": false
}
```

### 2.6 Vérifier si une page est en favoris
**Endpoint :** `GET /api/me-favorites/check/{pageId}`
**Description :** Vérifie si une page est dans les favoris de l'utilisateur
**Authentification :** Requise
**Paramètres :**
- `pageId` (integer, requis) : ID de la page

**Réponses :**
- **200 OK :** Statut du favori
- **401 Unauthorized :** Utilisateur non authentifié
- **404 Not Found :** Page non trouvée

**Exemple de réponse :**
```json
{
    "isFavorite": true,
    "pageId": 1
}
```

---

## 3. API Utilisateur

### 3.1 Informations de l'utilisateur connecté
**Endpoint :** `GET /user-api/me`
**Description :** Récupère les informations de l'utilisateur connecté
**Authentification :** Requise

**Exemple de réponse :**
```json
{
    "username": "john.doe@example.com",
    "roles": ["ROLE_USER"]
}
```

---

## Codes de statut HTTP utilisés

- **200 OK :** Requête réussie
- **201 Created :** Ressource créée avec succès
- **400 Bad Request :** Requête invalide (paramètres manquants)
- **401 Unauthorized :** Authentification requise
- **404 Not Found :** Ressource non trouvée
- **409 Conflict :** Conflit (ex: favori déjà existant)

---

## Notes d'utilisation pour n8n

1. **Sessions :** L'API utilise les sessions Symfony pour l'authentification. Assurez-vous de maintenir les cookies de session entre les requêtes.

2. **Content-Type :** Pour les requêtes POST avec du JSON, utilisez `Content-Type: application/json`.

3. **Sérialisation :** Les réponses sont automatiquement sérialisées en JSON par Symfony.

4. **Gestion des erreurs :** Toutes les erreurs renvoient un JSON avec un message explicatif.

5. **Paramètres de route :** Les paramètres dans l'URL (comme `{id}`) sont requis et doivent être des entiers positifs.

---

## 4. API Admin - Pages CRUD

### 4.1 Lister toutes les pages
**Endpoint :** `GET /api/admin/pages`
**Description :** Récupère la liste complète des pages
**Authentification :** Requise

### 4.2 Afficher une page spécifique
**Endpoint :** `GET /api/admin/pages/{id}`
**Description :** Récupère les détails d'une page par son ID

### 4.3 Créer une nouvelle page
**Endpoint :** `POST /api/admin/pages`
**Description :** Crée une nouvelle page
**Corps de la requête :**
```json
{
    "slug": "ma-nouvelle-page",
    "menuId": 1
}
```

### 4.4 Modifier une page existante
**Endpoint :** `PUT /api/admin/pages/{id}`
**Description :** Modifie une page existante
**Corps de la requête :**
```json
{
    "slug": "page-modifiee",
    "menuId": 2
}
```

### 4.5 Supprimer une page
**Endpoint :** `DELETE /api/admin/pages/{id}`
**Description :** Supprime une page (si aucun contenu associé)

---

## 5. API Admin - Contenus de cours CRUD

### 5.1 Lister tous les contenus
**Endpoint :** `GET /api/admin/page-contents`
**Description :** Récupère tous les contenus de cours

### 5.2 Afficher un contenu spécifique
**Endpoint :** `GET /api/admin/page-contents/{id}`
**Description :** Récupère un contenu par son ID

### 5.3 Créer un nouveau contenu
**Endpoint :** `POST /api/admin/page-contents`
**Description :** Crée un nouveau contenu de cours
**Corps de la requête :**
```json
{
    "title": "Titre du cours",
    "type": "lesson",
    "content": "Contenu HTML du cours",
    "code": "Code d'exemple",
    "pageId": 1,
    "categoryId": 2,
    "menuId": 3
}
```

### 5.4 Modifier un contenu existant
**Endpoint :** `PUT /api/admin/page-contents/{id}`
**Description :** Modifie un contenu existant

### 5.5 Supprimer un contenu
**Endpoint :** `DELETE /api/admin/page-contents/{id}`
**Description :** Supprime un contenu de cours

### 5.6 Contenus par page
**Endpoint :** `GET /api/admin/page-contents/by-page/{pageId}`
**Description :** Récupère tous les contenus d'une page spécifique

### 5.7 Contenus par catégorie
**Endpoint :** `GET /api/admin/page-contents/by-category/{categoryId}`
**Description :** Récupère tous les contenus d'une catégorie

---

## 6. API Admin - Catégories CRUD

### 6.1 Lister toutes les catégories
**Endpoint :** `GET /api/admin/categories`
**Description :** Récupère toutes les catégories

### 6.2 Afficher une catégorie spécifique
**Endpoint :** `GET /api/admin/categories/{id}`
**Description :** Récupère une catégorie par son ID

### 6.3 Créer une nouvelle catégorie
**Endpoint :** `POST /api/admin/categories`
**Description :** Crée une nouvelle catégorie
**Corps de la requête :**
```json
{
    "name": "Nom de la catégorie"
}
```

### 6.4 Modifier une catégorie existante
**Endpoint :** `PUT /api/admin/categories/{id}`
**Description :** Modifie une catégorie existante

### 6.5 Supprimer une catégorie
**Endpoint :** `DELETE /api/admin/categories/{id}`
**Description :** Supprime une catégorie (si aucun contenu associé)

### 6.6 Contenus d'une catégorie
**Endpoint :** `GET /api/admin/categories/{id}/page-contents`
**Description :** Récupère tous les contenus de la catégorie

### 6.7 Menus d'une catégorie
**Endpoint :** `GET /api/admin/categories/{id}/menus`
**Description :** Récupère tous les menus de la catégorie

---

## 🔐 Sécurité et bonnes pratiques

### Génération d'API Key sécurisée
```bash
# Générer une clé API sécurisée de 64 caractères
openssl rand -hex 32
```

### Configuration n8n
1. **Dans n8n**, configurez le header `X-API-KEY` dans vos requêtes HTTP
2. **Stockez la clé** dans les credentials n8n pour plus de sécurité
3. **Vérifiez** que l'utilisateur `API_ADMIN_USER_EMAIL` existe et a le rôle `ROLE_ADMIN`

### Restrictions de sécurité
- ✅ API Keys uniquement pour les routes `/api/admin/`
- ✅ Rôle `ROLE_ADMIN` requis pour l'utilisateur associé
- ✅ Authentification stateless (pas de session)
- ✅ Validation des relations entre entités avant suppression

### Codes d'erreur API Key
- **401 Unauthorized** : API key manquante ou invalide
- **403 Forbidden** : Utilisateur sans privilèges admin
- **404 Not Found** : Utilisateur associé à l'API key introuvable