# Documentation API pour MCP - Cours, Menu, Categorie, Exercice, QCM

Cette documentation decrit l'API REST disponible pour un serveur MCP (Model Context Protocol) permettant d'interagir avec les ressources educatives.

## Base URL

```
/api
```

---

## Table des matieres

1. [Categories](#categories)
2. [Menus](#menus)
3. [Exercices (Exo)](#exercices)
4. [Contenu des Exercices (ExoContent)](#contenu-des-exercices)
5. [QCM](#qcm)
6. [Relations entre entites](#relations-entre-entites)

---

## Categories

Les categories organisent le contenu en groupes thematiques.

### Endpoints

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/categories` | Liste toutes les categories |
| GET | `/api/categories/{id}` | Recupere une categorie par ID |
| POST | `/api/categories` | Cree une nouvelle categorie |
| PUT | `/api/categories/{id}` | Met a jour une categorie |
| DELETE | `/api/categories/{id}` | Supprime une categorie |

### Champs

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `name` | string | Nom de la categorie |
| `couleur` | string | Code couleur (ex: #FF5733) |
| `description` | text | Description de la categorie |
| `visible` | boolean | Visibilite (default: true) |
| `logo` | object | Logo associe |
| `seo` | object | Donnees SEO |
| `menus` | array | Menus associes |
| `exoMenus` | array | Menus d'exercices associes |

### Exemple de reponse

```json
{
  "@context": "/api/contexts/Category",
  "@id": "/api/categories/1",
  "@type": "Category",
  "id": 1,
  "name": "PHP",
  "couleur": "#8892BF",
  "description": "Cours et exercices PHP",
  "visible": true,
  "logo": {
    "@id": "/api/logos/1",
    "url": "/uploads/logos/php.png"
  },
  "menus": [
    "/api/menus/1",
    "/api/menus/2"
  ],
  "exoMenus": [
    "/api/exo_menus/1"
  ]
}
```

---

## Menus

Les menus permettent de structurer la navigation dans une categorie.

### Endpoints

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/admin/menus` | Liste tous les menus |
| GET | `/api/admin/menus/{id}` | Recupere un menu par ID |
| POST | `/api/admin/menus` | Cree un nouveau menu |
| PUT | `/api/admin/menus/{id}` | Met a jour un menu |
| DELETE | `/api/admin/menus/{id}` | Supprime un menu |

### Champs

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `label` | string | Libelle du menu |
| `category` | object/IRI | Categorie parente |
| `positionMenus` | object/IRI | Position d'affichage |
| `niveauCours` | object/IRI | Niveau de cours associe |
| `pages` | array | Pages associees |
| `pageContents` | array | Contenus de pages associes |

### Filtres disponibles

`GET /api/admin/menus` accepte aussi :

- `categoryId`
- `positionMenusId`
- `niveauCoursId`

### Exemple de reponse

```json
{
  "@context": "/api/contexts/Menus",
  "@id": "/api/admin/menus/1",
  "@type": "Menus",
  "id": 1,
  "label": "Les bases",
  "category": "/api/categories/1",
  "positionMenus": "/api/position_menus/1",
  "niveauCours": "/api/niveau_cours/1",
  "pages": [
    "/api/pages/1",
    "/api/pages/2"
  ]
}
```

---

## Exercices

### ExoMenu (Menu d'exercices)

Les menus d'exercices regroupent les exercices par theme.

#### Endpoints

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/exo_menus` | Liste tous les menus d'exercices |
| GET | `/api/exo_menus/{id}` | Recupere un menu d'exercice |
| POST | `/api/exo_menus` | Cree un menu d'exercice |
| PUT | `/api/exo_menus/{id}` | Met a jour un menu d'exercice |
| DELETE | `/api/exo_menus/{id}` | Supprime un menu d'exercice |

#### Champs ExoMenu

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `label` | string | Libelle du menu |
| `category` | object/IRI | Categorie parente |
| `exos` | array | Exercices associes |
| `exoContents` | array | Contenus d'exercices |

### Exo (Exercice)

Les exercices individuels.

#### Endpoints

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/exos` | Liste tous les exercices |
| GET | `/api/exos/{id}` | Recupere un exercice |
| POST | `/api/exos` | Cree un exercice |
| PUT | `/api/exos/{id}` | Met a jour un exercice |
| DELETE | `/api/exos/{id}` | Supprime un exercice |

#### Champs Exo

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `slug` | string | Identifiant URL-friendly |
| `exoMenu` | object/IRI | Menu d'exercice parent |
| `exoContents` | array | Contenus de l'exercice |

#### Exemple de reponse

```json
{
  "@context": "/api/contexts/Exo",
  "@id": "/api/exos/1",
  "@type": "Exo",
  "id": 1,
  "slug": "exercice-boucles-php",
  "exoMenu": "/api/exo_menus/1",
  "exoContents": [
    "/api/exo_contents/1",
    "/api/exo_contents/2"
  ]
}
```

---

## Contenu des Exercices

### ExoContent

Le contenu detaille d'un exercice.

#### Endpoints

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/exo_contents` | Liste tous les contenus |
| GET | `/api/exo_contents/{id}` | Recupere un contenu |
| GET | `/api/exo_contents?exo.slug={slug}` | Filtre par slug d'exercice |
| POST | `/api/exo_contents` | Cree un contenu |
| PUT | `/api/exo_contents/{id}` | Met a jour un contenu |
| DELETE | `/api/exo_contents/{id}` | Supprime un contenu |

#### Champs ExoContent

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `title` | string | Titre du contenu |
| `type` | string | Type de contenu |
| `content` | text | Contenu textuel |
| `code` | text | Code source |
| `exo` | object/IRI | Exercice parent |
| `category` | object/IRI | Categorie |
| `exoMenu` | object/IRI | Menu d'exercice |
| `exoBlocks` | array | Blocs de contenu |

### ExoBlock

Blocs de contenu pour structurer un exercice.

#### Endpoints

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/exo_blocks` | Liste tous les blocs |
| GET | `/api/exo_blocks/{id}` | Recupere un bloc |
| POST | `/api/exo_blocks` | Cree un bloc |
| PUT | `/api/exo_blocks/{id}` | Met a jour un bloc |
| DELETE | `/api/exo_blocks/{id}` | Supprime un bloc |

#### Champs ExoBlock

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `content` | text | Contenu du bloc |
| `code` | text | Code source |
| `type` | string | Type de bloc |
| `exoContent` | object/IRI | Contenu parent |

#### Exemple de reponse ExoContent

```json
{
  "@context": "/api/contexts/ExoContent",
  "@id": "/api/exo_contents/1",
  "@type": "ExoContent",
  "id": 1,
  "title": "Exercice 1 - Les boucles",
  "type": "exercice",
  "content": "Ecrire une boucle for qui affiche les nombres de 1 a 10",
  "code": "<?php\nfor ($i = 1; $i <= 10; $i++) {\n    echo $i;\n}\n?>",
  "exo": "/api/exos/1",
  "exoBlocks": [
    {
      "@id": "/api/exo_blocks/1",
      "content": "Indice: utilisez la structure for",
      "type": "hint"
    }
  ]
}
```

---

## QCM

Les QCM (Questions a Choix Multiples) pour evaluer les connaissances.

### Endpoints Standard

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/q_c_ms` | Liste tous les QCM |
| GET | `/api/q_c_ms/{id}` | Recupere un QCM |
| POST | `/api/q_c_ms` | Cree un QCM |
| PUT | `/api/q_c_ms/{id}` | Met a jour un QCM |
| DELETE | `/api/q_c_ms/{id}` | Supprime un QCM |

### Endpoints Personnalises

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/qcm` | Liste tous les QCM (format simplifie) |
| GET | `/api/qcm/{id}` | Recupere un QCM par ID |
| GET | `/api/qcm/language/{language}` | Filtre par langage (ex: PHP, JavaScript) |
| GET | `/api/qcm/difficulty/{difficulty}` | Filtre par difficulte |
| GET | `/api/qcm/language/{lang}/difficulty/{diff}` | Filtre combine |

### Filtres disponibles

```
GET /api/q_c_ms?languageQCM.name=PHP           # Par langage (insensible a la casse)
GET /api/q_c_ms?niveauQCM.titre=Debutant       # Par niveau (insensible a la casse)
```

### Champs QCM

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `titre` | string | Titre/Question du QCM |
| `solution` | text | Explication de la solution |
| `languageQCM` | object/IRI | Langage de programmation |
| `niveauQCM` | object/IRI | Niveau de difficulte |
| `choicesQCMs` | array | Choix de reponses |

### ChoicesQCM (Choix de reponse)

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `question` | string | Texte du choix |
| `isCorrect` | boolean | Si c'est la bonne reponse |
| `explication` | text | Explication du choix |
| `qcm` | object/IRI | QCM parent |

### LanguageQCM

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `name` | string | Nom du langage (PHP, JavaScript, Python...) |

### NiveauQCM

| Champ | Type | Description |
|-------|------|-------------|
| `id` | integer | Identifiant unique |
| `titre` | string | Niveau (Debutant, Intermediaire, Avance) |

### Exemple de reponse QCM

```json
{
  "@context": "/api/contexts/QCM",
  "@id": "/api/q_c_ms/1",
  "@type": "QCM",
  "id": 1,
  "titre": "Quelle fonction PHP permet de compter les elements d'un tableau?",
  "solution": "La fonction count() retourne le nombre d'elements dans un tableau.",
  "languageQCM": {
    "@id": "/api/language_q_c_ms/1",
    "name": "PHP"
  },
  "niveauQCM": {
    "@id": "/api/niveau_q_c_ms/1",
    "titre": "Debutant"
  },
  "choicesQCMs": [
    {
      "@id": "/api/choices_q_c_ms/1",
      "question": "count()",
      "isCorrect": true,
      "explication": "count() est la fonction standard pour compter les elements"
    },
    {
      "@id": "/api/choices_q_c_ms/2",
      "question": "length()",
      "isCorrect": false,
      "explication": "length() n'existe pas en PHP, c'est JavaScript"
    },
    {
      "@id": "/api/choices_q_c_ms/3",
      "question": "size()",
      "isCorrect": false,
      "explication": "size() n'existe pas en PHP"
    }
  ]
}
```

### Reponse format simplifie (endpoint custom)

```json
{
  "title": "Quelle fonction PHP permet de compter les elements d'un tableau?",
  "language": "PHP",
  "difficulty": "Debutant",
  "solution": "La fonction count() retourne le nombre d'elements dans un tableau.",
  "choices": [
    {
      "label": "count()",
      "isCorrect": true,
      "explanation": "count() est la fonction standard pour compter les elements"
    },
    {
      "label": "length()",
      "isCorrect": false,
      "explanation": "length() n'existe pas en PHP, c'est JavaScript"
    }
  ]
}
```

---

## Relations entre entites

```
Category
    |
    +-- 1:n --> Menus
    |              |
    |              +-- 1:n --> Pages
    |              +-- 1:n --> PageContents
    |
    +-- 1:n --> ExoMenu
    |              |
    |              +-- 1:n --> Exo
    |              |            |
    |              |            +-- 1:n --> ExoContent
    |              |                           |
    |              |                           +-- 1:n --> ExoBlock
    |              |
    |              +-- 1:n --> ExoContent
    |
    +-- 1:1 --> Logo
    +-- 1:1 --> Seo

QCM
    |
    +-- n:1 --> LanguageQCM
    +-- n:1 --> NiveauQCM
    +-- 1:n --> ChoicesQCM
```

---

## Pagination

### Configuration globale

- **Items par page par defaut**: 1000
- **Pagination activee**: Oui
- **Client peut modifier**: Oui

### Parametres de pagination

```
GET /api/categories?page=1                    # Page specifique
GET /api/categories?itemsPerPage=50           # Nombre d'items par page
```

### Exceptions

| Entite | Configuration |
|--------|---------------|
| QCM | 200 items/page (max 300) |
| ExoContent | Pagination desactivee |

---

## Groupes de serialisation

Les groupes controlent les champs exposes dans les reponses.

| Groupe | Utilise par | Description |
|--------|------------|-------------|
| `page_content:read` | Category, Menus, Page | Lecture contenu pages |
| `page_content:write` | Category, Menus, Page | Ecriture contenu pages |
| `exo_content:read` | Category, ExoMenu, Exo, ExoContent | Lecture exercices |
| `exo_content:write` | Category, ExoMenu, Exo, ExoContent | Ecriture exercices |
| `qcm:read` | QCM, LanguageQCM, NiveauQCM, ChoicesQCM | Lecture QCM |
| `qcm:write` | QCM | Ecriture QCM |
| `exo:read` | Exo | Lecture exercice |
| `exo_block:read` | ExoBlock | Lecture blocs |

---

## Exemples d'utilisation MCP

### Recuperer tous les QCM PHP niveau debutant

```bash
curl -X GET "https://api.example.com/api/qcm/language/PHP/difficulty/Debutant"
```

### Recuperer le contenu d'un exercice par slug

```bash
curl -X GET "https://api.example.com/api/exo_contents?exo.slug=exercice-boucles-php"
```

### Recuperer toutes les categories visibles

```bash
curl -X GET "https://api.example.com/api/categories?visible=true"
```

### Creer un nouveau QCM

```bash
curl -X POST "https://api.example.com/api/q_c_ms" \
  -H "Content-Type: application/ld+json" \
  -d '{
    "titre": "Question sur les tableaux",
    "solution": "Explication de la solution",
    "languageQCM": "/api/language_q_c_ms/1",
    "niveauQCM": "/api/niveau_q_c_ms/1"
  }'
```

---

## Codes de reponse HTTP

| Code | Description |
|------|-------------|
| 200 | Succes (GET, PUT) |
| 201 | Cree avec succes (POST) |
| 204 | Supprime avec succes (DELETE) |
| 400 | Requete invalide |
| 404 | Ressource non trouvee |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

---

## Notes importantes

1. **Format de donnees**: L'API utilise JSON-LD avec le contexte Hydra
2. **IRI vs Objets**: Les relations peuvent etre retournees comme IRI (`/api/categories/1`) ou objets selon les groupes de serialisation
3. **Cascade Delete**: La suppression d'un QCM supprime automatiquement ses ChoicesQCM
4. **Visibilite**: Le champ `visible` permet de filtrer le contenu affiche en frontend
