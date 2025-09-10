# API Documentation pour n8n

Ce document détaille tous les endpoints API disponibles dans l'application Symfony pour une intégration avec n8n.

## Base URL
```
http://votre-domaine.com
```

## Authentification
La plupart des endpoints nécessitent une authentification utilisateur. L'utilisateur doit être connecté via les sessions Symfony.

---

## ⚠️ IMPORTANT - Routes CRUD manquantes

**Actuellement, l'application ne dispose PAS de routes API publiques pour :**
- Créer/Modifier/Supprimer des pages (CREATE/UPDATE/DELETE pour les pages)
- Créer/Modifier/Supprimer des cours (CREATE/UPDATE/DELETE pour les contenus de cours)

**Routes disponibles seulement :**
- Interface d'administration EasyAdmin (`/admin`) - interface web uniquement, pas d'API
- Lecture seule des pages via `/api/`

**Pour n8n, vous devrez créer des contrôleurs API supplémentaires si vous souhaitez :**
- Automatiser la création de pages/cours
- Modifier le contenu existant
- Supprimer des éléments

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

## Routes CRUD suggérées à implémenter

Si vous souhaitez une API complète pour n8n, voici les endpoints qu'il faudrait créer :

### API Pages CRUD
```
POST   /api/pages              - Créer une nouvelle page
PUT    /api/pages/{id}         - Modifier une page existante  
DELETE /api/pages/{id}         - Supprimer une page
```

### API Contenus de cours CRUD
```
POST   /api/page-contents      - Créer un nouveau contenu de cours
PUT    /api/page-contents/{id} - Modifier un contenu de cours
DELETE /api/page-contents/{id} - Supprimer un contenu de cours
GET    /api/page-contents      - Lister tous les contenus (pas encore implémenté)
GET    /api/page-contents/{id} - Afficher un contenu spécifique (pas encore implémenté)
```

### API Catégories CRUD  
```
GET    /api/categories         - Lister toutes les catégories
POST   /api/categories         - Créer une nouvelle catégorie
PUT    /api/categories/{id}    - Modifier une catégorie
DELETE /api/categories/{id}    - Supprimer une catégorie
```

**Note :** Ces routes n'existent pas encore et devront être implémentées selon vos besoins d'automatisation avec n8n.