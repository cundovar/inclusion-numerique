# AppSelect - Composant de sélection réutilisable

## Description
Composant de sélection modulable et réutilisable basé sur Element Plus, supportant les selects simples, multiples et cascades.

## Props

### `modelValue` (String, Number, Array, Boolean)
Valeur sélectionnée (v-model)
- **Défaut**: `null`

### `cascader` (Boolean)
Utiliser un cascader (sélection en cascade) au lieu d'un select simple
- **Défaut**: `false`

### `label` (String)
Label du select
- **Défaut**: `null`

### `placeholder` (String)
Texte de placeholder
- **Défaut**: `'Sélectionnez'`

### `options` (Array)
Options du select
- **Format simple**: `['Option 1', 'Option 2']`
- **Format objet**: `[{ label: 'Option 1', value: 1 }]`
- **Défaut**: `[]`

### `cascaderProps` (Object)
Configuration des propriétés pour le cascader
- **Défaut**: `{ value: 'value', label: 'label', children: 'children' }`

### `optionLabel` (String)
Nom de la propriété pour le label des options
- **Défaut**: `'label'`

### `optionValue` (String)
Nom de la propriété pour la valeur des options
- **Défaut**: `'value'`

### `size` (String)
Taille du select
- **Options**: `'large'`, `'default'`, `'small'`
- **Défaut**: `'default'`

### `multiple` (Boolean)
Sélection multiple
- **Défaut**: `false`

### `filterable` (Boolean)
Activer la recherche
- **Défaut**: `false`

### `clearable` (Boolean)
Bouton pour effacer la sélection
- **Défaut**: `false`

### `disabled` (Boolean)
Désactiver le select
- **Défaut**: `false`

### `required` (Boolean)
Champ requis (affiche un astérisque rouge)
- **Défaut**: `false`

### `collapseTags` (Boolean)
Réduire les tags en mode multiple
- **Défaut**: `false`

### `collapseTagsTooltip` (Boolean)
Afficher un tooltip pour les tags réduits
- **Défaut**: `false`

### `showAllLevels` (Boolean)
Afficher tous les niveaux dans le cascader
- **Défaut**: `true`

### `error` (String)
Message d'erreur
- **Défaut**: `null`

### `helpText` (String)
Message d'aide
- **Défaut**: `null`

### `id` (String)
ID unique du select
- **Défaut**: `null` (généré automatiquement)

## Events

### `@update:modelValue`
Émis lors de la modification de la valeur

### `@change`
Émis lors du changement de sélection

### `@blur`
Émis lors de la perte de focus

### `@focus`
Émis lors de l'obtention du focus

## Exemples d'utilisation

### Select simple avec tableau de strings
```vue
<script setup>
import { ref } from 'vue'
import AppSelect from '@/components/commun/select/AppSelect.vue'

const color = ref('')
const colors = ['Rouge', 'Vert', 'Bleu', 'Jaune']
</script>

<template>
  <AppSelect
    v-model="color"
    label="Couleur préférée"
    :options="colors"
    clearable
  />
</template>
```

### Select avec objets
```vue
<script setup>
const country = ref('')
const countries = [
  { label: 'France', value: 'fr' },
  { label: 'Espagne', value: 'es' },
  { label: 'Italie', value: 'it' },
  { label: 'Allemagne', value: 'de' }
]
</script>

<template>
  <AppSelect
    v-model="country"
    label="Pays"
    placeholder="Sélectionnez un pays"
    :options="countries"
    clearable
  />
</template>
```

### Select avec propriétés personnalisées
```vue
<script setup>
const city = ref('')
const cities = [
  { name: 'Paris', id: 1 },
  { name: 'Lyon', id: 2 },
  { name: 'Marseille', id: 3 }
]
</script>

<template>
  <AppSelect
    v-model="city"
    label="Ville"
    :options="cities"
    option-label="name"
    option-value="id"
    clearable
  />
</template>
```

### Select multiple
```vue
<script setup>
const skills = ref([])
const skillOptions = [
  { label: 'JavaScript', value: 'js' },
  { label: 'Python', value: 'py' },
  { label: 'PHP', value: 'php' },
  { label: 'Java', value: 'java' }
]
</script>

<template>
  <AppSelect
    v-model="skills"
    label="Compétences"
    :options="skillOptions"
    multiple
    collapse-tags
    collapse-tags-tooltip
    clearable
  />
</template>
```

### Select avec recherche
```vue
<AppSelect
  v-model="framework"
  label="Framework"
  :options="frameworks"
  filterable
  placeholder="Rechercher un framework"
  clearable
/>
```

### Cascader (sélection en cascade)
```vue
<script setup>
const location = ref([])
const locationOptions = [
  {
    label: 'France',
    value: 'fr',
    children: [
      {
        label: 'Île-de-France',
        value: 'idf',
        children: [
          { label: 'Paris', value: 'paris' },
          { label: 'Versailles', value: 'versailles' }
        ]
      },
      {
        label: 'Rhône-Alpes',
        value: 'ra',
        children: [
          { label: 'Lyon', value: 'lyon' },
          { label: 'Grenoble', value: 'grenoble' }
        ]
      }
    ]
  },
  {
    label: 'Espagne',
    value: 'es',
    children: [
      {
        label: 'Catalogne',
        value: 'cat',
        children: [
          { label: 'Barcelone', value: 'barcelona' }
        ]
      }
    ]
  }
]
</script>

<template>
  <AppSelect
    v-model="location"
    cascader
    label="Localisation"
    :options="locationOptions"
    placeholder="Sélectionnez une localisation"
    clearable
  />
</template>
```

### Cascader avec props personnalisés
```vue
<script setup>
const category = ref([])
const categories = [
  {
    titre: 'Tech',
    id: 1,
    subcategories: [
      { titre: 'JavaScript', id: 11 },
      { titre: 'Python', id: 12 }
    ]
  }
]
</script>

<template>
  <AppSelect
    v-model="category"
    cascader
    label="Catégorie"
    :options="categories"
    :cascader-props="{
      value: 'id',
      label: 'titre',
      children: 'subcategories'
    }"
    clearable
  />
</template>
```

### Select avec validation
```vue
<script setup>
const country = ref('')
const error = ref('')

const validateCountry = () => {
  if (!country.value) {
    error.value = 'Veuillez sélectionner un pays'
  } else {
    error.value = ''
  }
}
</script>

<template>
  <AppSelect
    v-model="country"
    label="Pays"
    :options="countries"
    :error="error"
    @blur="validateCountry"
    required
  />
</template>
```

### Select avec message d'aide
```vue
<AppSelect
  v-model="level"
  label="Niveau"
  :options="levels"
  help-text="Sélectionnez votre niveau d'expérience"
/>
```

### Différentes tailles
```vue
<!-- Large -->
<AppSelect
  v-model="value1"
  :options="options"
  size="large"
/>

<!-- Default -->
<AppSelect
  v-model="value2"
  :options="options"
  size="default"
/>

<!-- Small -->
<AppSelect
  v-model="value3"
  :options="options"
  size="small"
/>
```

### Select disabled
```vue
<AppSelect
  v-model="value"
  label="Sélection désactivée"
  :options="options"
  disabled
/>
```

## Utilisation dans vos composants

```vue
<script setup>
import { ref } from 'vue'
import AppSelect from '@/components/commun/select/AppSelect.vue'

const formData = ref({
  country: '',
  skills: [],
  location: []
})

const countries = [
  { label: 'France', value: 'fr' },
  { label: 'Espagne', value: 'es' }
]

const skills = [
  { label: 'JavaScript', value: 'js' },
  { label: 'Python', value: 'py' }
]

const locations = [
  {
    label: 'Europe',
    value: 'eu',
    children: [
      { label: 'France', value: 'fr' },
      { label: 'Espagne', value: 'es' }
    ]
  }
]
</script>

<template>
  <div class="form">
    <!-- Select simple -->
    <AppSelect
      v-model="formData.country"
      label="Pays"
      :options="countries"
      clearable
    />

    <!-- Select multiple -->
    <AppSelect
      v-model="formData.skills"
      label="Compétences"
      :options="skills"
      multiple
      filterable
    />

    <!-- Cascader -->
    <AppSelect
      v-model="formData.location"
      cascader
      label="Localisation"
      :options="locations"
      clearable
    />
  </div>
</template>
```
