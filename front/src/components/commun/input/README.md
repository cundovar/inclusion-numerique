# AppInput - Composant d'input réutilisable

## Description
Composant d'input modulable et réutilisable basé sur Element Plus, supportant différents types d'inputs (texte, textarea, select, etc.).

## Props

### `modelValue` (String, Number, Array)
Valeur de l'input (v-model)
- **Défaut**: `''`

### `type` (String)
Type d'input
- **Options**: `'text'`, `'password'`, `'email'`, `'number'`, `'textarea'`, `'select'`
- **Défaut**: `'text'`

### `label` (String)
Label de l'input
- **Défaut**: `null`

### `placeholder` (String)
Texte de placeholder
- **Défaut**: `''`

### `size` (String)
Taille de l'input
- **Options**: `'large'`, `'default'`, `'small'`
- **Défaut**: `'default'`

### `disabled` (Boolean)
Désactiver l'input
- **Défaut**: `false`

### `readonly` (Boolean)
Input en lecture seule
- **Défaut**: `false`

### `required` (Boolean)
Champ requis (affiche un astérisque rouge)
- **Défaut**: `false`

### `clearable` (Boolean)
Afficher le bouton pour effacer
- **Défaut**: `false`

### `maxlength` (Number)
Nombre maximum de caractères
- **Défaut**: `null`

### `prefixIcon` (String)
Icône à gauche (classe Element Plus)
- **Défaut**: `null`

### `suffixIcon` (String)
Icône à droite (classe Element Plus)
- **Défaut**: `null`

### `options` (Array)
Options pour le select
- **Format**: `[{ label: 'Label', value: 'value', disabled: false }]`
- **Défaut**: `[]`

### `multiple` (Boolean)
Sélection multiple (pour select)
- **Défaut**: `false`

### `filterable` (Boolean)
Select avec recherche
- **Défaut**: `false`

### `rows` (Number)
Nombre de lignes (pour textarea)
- **Défaut**: `3`

### `autosize` (Boolean, Object)
Taille automatique (pour textarea)
- **Défaut**: `false`

### `showWordLimit` (Boolean)
Afficher le compteur de caractères
- **Défaut**: `false`

### `error` (String)
Message d'erreur
- **Défaut**: `null`

### `helpText` (String)
Message d'aide
- **Défaut**: `null`

### `id` (String)
ID unique de l'input
- **Défaut**: `null` (généré automatiquement)

## Events

### `@update:modelValue`
Émis lors de la modification de la valeur

### `@change`
Émis lors du changement de valeur

### `@input`
Émis lors de la saisie

### `@blur`
Émis lors de la perte de focus

### `@focus`
Émis lors de l'obtention du focus

## Exemples d'utilisation

### Input texte simple
```vue
<AppInput
  v-model="username"
  label="Nom d'utilisateur"
  placeholder="Entrez votre nom"
/>
```

### Input avec icônes
```vue
<AppInput
  v-model="email"
  type="email"
  label="Email"
  placeholder="exemple@email.com"
  prefix-icon="Message"
  clearable
/>
```

### Input password
```vue
<AppInput
  v-model="password"
  type="password"
  label="Mot de passe"
  placeholder="Entrez votre mot de passe"
  required
/>
```

### Input number
```vue
<AppInput
  v-model="age"
  type="number"
  label="Âge"
  placeholder="Votre âge"
/>
```

### Textarea
```vue
<AppInput
  v-model="description"
  type="textarea"
  label="Description"
  placeholder="Décrivez-vous"
  :rows="5"
  :maxlength="500"
  show-word-limit
/>
```

### Textarea avec autosize
```vue
<AppInput
  v-model="comment"
  type="textarea"
  label="Commentaire"
  :autosize="{ minRows: 2, maxRows: 6 }"
/>
```

### Select simple
```vue
<script setup>
const country = ref('')
const countries = [
  { label: 'France', value: 'fr' },
  { label: 'Espagne', value: 'es' },
  { label: 'Italie', value: 'it' }
]
</script>

<template>
  <AppInput
    v-model="country"
    type="select"
    label="Pays"
    placeholder="Sélectionnez un pays"
    :options="countries"
    clearable
  />
</template>
```

### Select multiple avec recherche
```vue
<AppInput
  v-model="selectedSkills"
  type="select"
  label="Compétences"
  placeholder="Sélectionnez vos compétences"
  :options="skills"
  multiple
  filterable
/>
```

### Input avec validation
```vue
<script setup>
const email = ref('')
const emailError = ref('')

const validateEmail = () => {
  if (!email.value.includes('@')) {
    emailError.value = 'Email invalide'
  } else {
    emailError.value = ''
  }
}
</script>

<template>
  <AppInput
    v-model="email"
    type="email"
    label="Email"
    placeholder="exemple@email.com"
    :error="emailError"
    @blur="validateEmail"
    required
  />
</template>
```

### Input avec message d'aide
```vue
<AppInput
  v-model="username"
  label="Nom d'utilisateur"
  placeholder="Choisissez un nom"
  help-text="Le nom d'utilisateur doit contenir entre 3 et 20 caractères"
  :maxlength="20"
/>
```

### Différentes tailles
```vue
<!-- Large -->
<AppInput
  v-model="text"
  size="large"
  placeholder="Large"
/>

<!-- Default -->
<AppInput
  v-model="text"
  size="default"
  placeholder="Default"
/>

<!-- Small -->
<AppInput
  v-model="text"
  size="small"
  placeholder="Small"
/>
```

### Input disabled
```vue
<AppInput
  v-model="value"
  label="Champ désactivé"
  disabled
/>
```

### Input readonly
```vue
<AppInput
  v-model="value"
  label="Champ en lecture seule"
  readonly
/>
```

## Utilisation dans vos composants

```vue
<script setup>
import { ref } from 'vue'
import AppInput from '@/components/commun/input/AppInput.vue'

const formData = ref({
  name: '',
  email: '',
  country: '',
  bio: ''
})

const countries = [
  { label: 'France', value: 'fr' },
  { label: 'Espagne', value: 'es' },
  { label: 'Italie', value: 'it' }
]
</script>

<template>
  <div class="form">
    <AppInput
      v-model="formData.name"
      label="Nom"
      placeholder="Votre nom"
      required
    />

    <AppInput
      v-model="formData.email"
      type="email"
      label="Email"
      placeholder="exemple@email.com"
      clearable
      required
    />

    <AppInput
      v-model="formData.country"
      type="select"
      label="Pays"
      :options="countries"
      clearable
    />

    <AppInput
      v-model="formData.bio"
      type="textarea"
      label="Biographie"
      :rows="4"
      :maxlength="500"
      show-word-limit
    />
  </div>
</template>
```
