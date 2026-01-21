# Filtrage des Catégories/Menus/PageContents par Niveau de Cours

## Objectif

Ajouter des boutons "Junior" / "Intermédiaire" / "Tous" réutilisables sur plusieurs pages pour filtrer les données selon leur niveau.

---

## Architecture globale

```
┌─────────────────────────────────────────────────────────────┐
│                         BACKEND                              │
│                                                              │
│  NiveauCours.php → Ajouter Groups sur id et name            │
│                                                              │
│  API Response:                                               │
│  GET /api/categories   → { niveauCours: { name: "Junior" }} │
│  GET /api/menus        → { niveauCours: { name: "Junior" }} │
│  GET /api/page_contents→ { niveauCours: { name: "Junior" }} │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                         FRONTEND                             │
│                                                              │
│  composables/api/useNiveauApi.js  ← Appels API centralisés  │
│              ↓                                               │
│  stores/niveauStore.js            ← État Pinia partagé      │
│              ↓                                               │
│  components/ui/NiveauFilter.vue   ← Composant UI (AppButton)│
│              ↓                                               │
│  Pages (HomePage, CategoryPage...)← Filtrent leurs données  │
└─────────────────────────────────────────────────────────────┘
```

---

## 1. Backend (Symfony)

### Modification à faire

**Fichier :** `src/Entity/NiveauCours.php`

Ajouter les groupes de sérialisation sur `id` et `name` :

```php
<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups; // ← Ajouter cet import

// ... autres imports ...

class NiveauCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['page_content:read'])]  // ← Ajouter
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['page_content:read'])]  // ← Ajouter
    private ?string $name = null;

    // ... reste du code ...
}
```

### Résultat API

Avant :
```json
{
  "id": 1,
  "name": "JavaScript",
  "niveauCours": {}
}
```

Après :
```json
{
  "id": 1,
  "name": "JavaScript",
  "niveauCours": {
    "id": 1,
    "name": "Junior"
  }
}
```

### Toutes les entités concernées

```json
// GET /api/categories
{
  "name": "JavaScript",
  "niveauCours": { "id": 1, "name": "Junior" }
}

// GET /api/menus
{
  "label": "Les bases JS",
  "niveauCours": { "id": 1, "name": "Junior" }
}

// GET /api/page_contents
{
  "title": "Variables",
  "niveauCours": { "id": 2, "name": "Intermédiaire" }
}
```

---

## 2. Frontend - Structure des fichiers

```
front/src/
├── services/
│   └── api.js                   ← Instance axios (déjà existant)
│
├── composables/
│   └── api/
│       └── useNiveauCoursApi.js ← Appel API centralisé
│
├── stores/
│   └── niveauStore.js           ← État Pinia (niveaux + selectedNiveau)
│
└── components/
    └── ui/
        └── NiveauFilter.vue     ← Composant UI (utilise AppButton)
```

---

## 3. Frontend - Composable API

**Fichier :** `front/src/composables/api/useNiveauCoursApi.js`

```javascript
import api from '@/services/api'  // ← Instance axios configurée

export function useNiveauCoursApi() {

  const getNiveaux = async () => {
    const response = await api.get('/niveau_cours')  // baseURL déjà /api
    return response.data['hydra:member'] || response.data
  }

  return {
    getNiveaux
  }
}
```

> **Note :** `api` est l'instance axios définie dans `services/api.js` avec :
> - `baseURL: '/api'` → pas besoin de répéter `/api`
> - Headers JSON-LD pour API Platform
> - Intercepteurs pour le token et les erreurs

---

## 4. Frontend - Store Pinia

**Fichier :** `front/src/stores/niveauStore.js`

```javascript
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useNiveauCoursApi } from '@/composables/api/useNiveauCoursApi'

export const useNiveauStore = defineStore('niveau', () => {
  // État
  const niveaux = ref([])
  const selectedNiveau = ref(null)  // null = tous, "Junior", "Intermédiaire"
  const loading = ref(false)

  // API
  const { getNiveaux } = useNiveauCoursApi()

  // Actions
  async function fetchNiveaux() {
    if (niveaux.value.length > 0) return  // Déjà chargé

    loading.value = true
    try {
      niveaux.value = await getNiveaux()
    } catch (error) {
      console.error('Erreur chargement niveaux:', error)
    } finally {
      loading.value = false
    }
  }

  function setNiveau(niveau) {
    selectedNiveau.value = selectedNiveau.value === niveau ? null : niveau
  }

  function resetNiveau() {
    selectedNiveau.value = null
  }

  return {
    // État
    niveaux,
    selectedNiveau,
    loading,
    // Actions
    fetchNiveaux,
    setNiveau,
    resetNiveau
  }
})
```

---

## 5. Frontend - Composant UI

**Fichier :** `front/src/components/ui/NiveauFilter.vue`

```vue
<template>
  <div class="niveau-filter flex gap-2">
    <!-- Bouton "Tous" -->
    <AppButton
      :variant="modelValue === null ? 'primary' : 'outline'"
      size="sm"
      @click="$emit('update:modelValue', null)"
    >
      Tous
    </AppButton>

    <!-- Boutons dynamiques pour chaque niveau -->
    <AppButton
      v-for="niveau in niveaux"
      :key="niveau.id"
      :variant="modelValue === niveau.name ? 'primary' : 'outline'"
      size="sm"
      @click="$emit('update:modelValue', niveau.name)"
    >
      {{ niveau.name }}
    </AppButton>
  </div>
</template>

<script setup>
import AppButton from '@/components/ui/AppButton.vue'

defineProps({
  // Liste des niveaux disponibles
  niveaux: {
    type: Array,
    required: true
  },
  // Niveau actuellement sélectionné (v-model)
  modelValue: {
    type: String,
    default: null
  }
})

defineEmits(['update:modelValue'])
</script>
```

### Logique visuelle des boutons

| État | Variante AppButton | Apparence |
|------|-------------------|-----------|
| Sélectionné | `primary` | Bleu plein |
| Non sélectionné | `outline` | Bordure bleue, fond transparent |

```
Exemple avec "Intermédiaire" sélectionné :

[ Tous ]  [ Junior ]  [ Intermédiaire ]
 outline    outline       primary
                        (sélectionné)
```

---

## 6. Frontend - Utilisation dans les pages

### HomePage.vue

```vue
<template>
  <div>
    <!-- Filtre par niveau -->
    <NiveauFilter
      :niveaux="niveauStore.niveaux"
      v-model="niveauStore.selectedNiveau"
    />

    <!-- Grille de catégories filtrées -->
    <div class="categories-grid">
      <div v-for="category in filteredCats" :key="category.id">
        <!-- ... contenu ... -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useNiveauStore } from '@/stores/niveauStore'
import { useData } from '@/utils/fetchDataPwa'
import NiveauFilter from '@/components/ui/NiveauFilter.vue'

const niveauStore = useNiveauStore()
const { cats } = useData()

// Charger les niveaux au montage
onMounted(() => {
  niveauStore.fetchNiveaux()
})

// Catégories filtrées par niveau
const filteredCats = computed(() => {
  if (!niveauStore.selectedNiveau) {
    return cats.value
  }
  return cats.value.filter(c => c.niveauCours?.name === niveauStore.selectedNiveau)
})
</script>
```

### CategoryPage.vue

```vue
<script setup>
import { computed, onMounted } from 'vue'
import { useNiveauStore } from '@/stores/niveauStore'

const niveauStore = useNiveauStore()

onMounted(() => {
  niveauStore.fetchNiveaux()
})

// Menus filtrés par niveau
const filteredMenus = computed(() => {
  if (!niveauStore.selectedNiveau) {
    return menus.value
  }
  return menus.value.filter(m => m.niveauCours?.name === niveauStore.selectedNiveau)
})
</script>
```

### Autre page avec PageContents

```javascript
// Filtrer les pageContents
const filteredContents = computed(() => {
  if (!niveauStore.selectedNiveau) {
    return contents.value
  }
  return contents.value.filter(p => p.niveauCours?.name === niveauStore.selectedNiveau)
})
```

---

## 7. Flux des données

```
1. Page monte
   └─→ niveauStore.fetchNiveaux()
       └─→ useNiveauCoursApi().getNiveaux()
           └─→ GET /api/niveau_cours
               └─→ niveauStore.niveaux = [{ id: 1, name: "Junior" }, ...]

2. User clique "Junior"
   └─→ NiveauFilter émet update:modelValue("Junior")
       └─→ niveauStore.selectedNiveau = "Junior"
           └─→ Toutes les pages voient le changement (réactif Pinia)

3. Chaque page filtre ses données
   └─→ computed filteredXXX recalculé automatiquement
       └─→ Affichage mis à jour
```

---

## 8. Avantages de cette architecture

| Couche | Responsabilité | Avantage |
|--------|----------------|----------|
| `useNiveauApi` | Appels HTTP | Un seul endroit pour l'endpoint |
| `niveauStore` | État global Pinia | Partagé entre toutes les pages |
| `NiveauFilter` | Affichage boutons | Réutilisable, utilise AppButton |
| Pages | Filtrage des données | Chaque page filtre ses propres données |

### Séparation des responsabilités

```
NiveauFilter (UI)
├── Ne connaît PAS les données à filtrer
├── Affiche juste des boutons
└── Émet la sélection

Page (Logique métier)
├── Connaît SES données (cats, menus, contents)
├── Utilise selectedNiveau du store
└── Filtre avec un computed
```

---

## 9. Checklist d'implémentation

### Backend
- [ ] Ajouter `use Symfony\Component\Serializer\Annotation\Groups;` dans NiveauCours.php
- [ ] Ajouter `#[Groups(['page_content:read'])]` sur `$id`
- [ ] Ajouter `#[Groups(['page_content:read'])]` sur `$name`
- [ ] Vider le cache (`php bin/console cache:clear`)
- [ ] Tester l'API : `GET /api/categories` doit retourner `niveauCours.name`

### Frontend
- [ ] Compléter `composables/api/useNiveauCoursApi.js`
- [ ] Créer `stores/niveauStore.js`
- [ ] Créer `components/ui/NiveauFilter.vue`
- [ ] Intégrer dans HomePage.vue
- [ ] Intégrer dans autres pages si nécessaire
- [ ] Tester le filtrage

---

## 10. Persistance avec Pinia Plugin

### Pourquoi un plugin ?

Le store Pinia est en mémoire → le choix de l'utilisateur est perdu au rechargement.
Avec le plugin, les données sont sauvegardées dans localStorage automatiquement.

### Installation

```bash
npm install pinia-plugin-persistedstate
```

### Configuration (une seule fois)

**Fichier :** `front/src/main.js`

```javascript
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)  // ← Ajouter

app.use(pinia)
```

### Utilisation dans les stores

```javascript
// stores/niveauStore.js
export const useNiveauStore = defineStore('niveau', () => {
  const niveaux = ref([])
  const selectedNiveau = ref(null)
  // ...
}, {
  persist: true  // ← Ajouter pour activer la persistance
})
```

### Stores multiples

```javascript
// Store persisté
export const useNiveauStore = defineStore('niveau', () => {
  // ...
}, {
  persist: true  // ✅ Sauvegardé dans localStorage
})

// Store non persisté
export const useTempStore = defineStore('temp', () => {
  // ...
})  // ❌ Pas de persist = perdu au refresh
```

### Options avancées

```javascript
{
  persist: {
    key: 'niveau-filter',        // Nom personnalisé dans localStorage
    storage: sessionStorage,      // sessionStorage au lieu de localStorage
    pick: ['selectedNiveau'],     // Persister seulement certains champs
  }
}
```

### Durée de stockage

| Stockage | Durée | Supprimé quand... |
|----------|-------|-------------------|
| localStorage (défaut) | **Illimitée** | User vide le cache, ou code le supprime |
| sessionStorage | Session | User ferme l'onglet/navigateur |

### Store complet avec persistance

**Fichier :** `front/src/stores/niveauStore.js`

```javascript
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useNiveauCoursApi } from '@/composables/api/useNiveauCoursApi'

export const useNiveauStore = defineStore('niveau', () => {
  // État
  const niveaux = ref([])
  const selectedNiveau = ref(null)
  const loading = ref(false)

  // API
  const { getNiveaux } = useNiveauCoursApi()

  // Actions
  async function fetchNiveaux() {
    if (niveaux.value.length > 0) return

    loading.value = true
    try {
      niveaux.value = await getNiveaux()
    } catch (error) {
      console.error('Erreur chargement niveaux:', error)
    } finally {
      loading.value = false
    }
  }

  function setNiveau(niveau) {
    selectedNiveau.value = selectedNiveau.value === niveau ? null : niveau
  }

  function resetNiveau() {
    selectedNiveau.value = null
  }

  return {
    niveaux,
    selectedNiveau,
    loading,
    fetchNiveaux,
    setNiveau,
    resetNiveau
  }
}, {
  persist: {
    pick: ['selectedNiveau']  // Persister seulement la sélection, pas la liste
  }
})
```

---

## 11. Pour aller plus loin (optionnel)

### Fonction utilitaire de filtrage

Si la logique de filtrage devient répétitive, créer une fonction :

**Fichier :** `front/src/utils/filters.js`

```javascript
/**
 * Filtre un tableau par niveau de cours
 * @param {Array} items - Tableau à filtrer
 * @param {string|null} niveau - Niveau sélectionné
 * @param {string} path - Chemin vers niveauCours (défaut: "niveauCours")
 */
export const filterByNiveau = (items, niveau, path = 'niveauCours') => {
  if (!niveau) return items

  return items.filter(item => {
    const niveauCours = path.split('.').reduce((obj, key) => obj?.[key], item)
    return niveauCours?.name === niveau
  })
}
```

**Utilisation :**

```javascript
import { filterByNiveau } from '@/utils/filters'

const filteredCats = computed(() =>
  filterByNiveau(cats.value, niveauStore.selectedNiveau)
)
```
