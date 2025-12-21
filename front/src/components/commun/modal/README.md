# AppModal

Composant de modal/popup réutilisable et personnalisable avec support pour différentes tailles, positions et animations.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | Boolean | `false` | Contrôle l'affichage de la modal (v-model) |
| `title` | String | `''` | Titre de la modal |
| `icon` | String | `null` | Icône affichée dans le header |
| `size` | String | `'md'` | Taille: `xs`, `sm`, `md`, `lg`, `xl`, `full` |
| `position` | String | `'center'` | Position: `center`, `top`, `bottom` |
| `closable` | Boolean | `true` | Afficher le bouton de fermeture |
| `closeOnBackdrop` | Boolean | `true` | Fermer au clic sur le backdrop |
| `closeOnEscape` | Boolean | `true` | Fermer avec la touche Escape |
| `persistent` | Boolean | `false` | Empêche toute fermeture (backdrop, escape, bouton) |
| `showHeader` | Boolean | `true` | Afficher le header |
| `showFooter` | Boolean | `false` | Afficher le footer |
| `bodyPadding` | String | `'md'` | Padding du body: `none`, `sm`, `md`, `lg`, `xl` |
| `backdropBlur` | Boolean | `true` | Appliquer un flou au backdrop |
| `showCancelButton` | Boolean | `false` | Afficher bouton annuler dans footer |
| `showConfirmButton` | Boolean | `false` | Afficher bouton confirmer dans footer |
| `cancelText` | String | `'Annuler'` | Texte du bouton annuler |
| `confirmText` | String | `'Confirmer'` | Texte du bouton confirmer |
| `confirmVariant` | String | `'primary'` | Variante du bouton confirmer |
| `loading` | Boolean | `false` | État loading du bouton confirmer |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | Boolean | Émis pour le v-model |
| `open` | - | Émis à l'ouverture de la modal |
| `close` | - | Émis à la fermeture de la modal |
| `confirm` | - | Émis au clic sur le bouton confirmer |
| `cancel` | - | Émis au clic sur annuler ou fermer |

## Slots

| Slot | Props | Description |
|------|-------|-------------|
| `header` | - | Remplace tout le contenu du header |
| `default` | - | Contenu principal de la modal |
| `footer` | - | Remplace tout le contenu du footer |

## Exemples d'utilisation

### Modal simple

```vue
<template>
  <div>
    <AppButton @click="isOpen = true" text-content="Ouvrir la modal" />

    <AppModal
      v-model="isOpen"
      title="Ma Modal"
      closable
    >
      <p>Contenu de la modal</p>
    </AppModal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AppModal from '@/components/commun/modal/AppModal.vue'
import AppButton from '@/components/commun/button/AppButton.vue'

const isOpen = ref(false)
</script>
```

### Modal de confirmation

```vue
<template>
  <AppModal
    v-model="showConfirm"
    title="Confirmer la suppression"
    icon="pi pi-exclamation-triangle"
    size="sm"
    show-cancel-button
    show-confirm-button
    confirm-text="Supprimer"
    confirm-variant="danger"
    cancel-text="Annuler"
    @confirm="handleDelete"
    @cancel="showConfirm = false"
  >
    <p>Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.</p>
  </AppModal>
</template>

<script setup>
import { ref } from 'vue'

const showConfirm = ref(false)

const handleDelete = () => {
  console.log('Suppression confirmée')
  showConfirm.value = false
}
</script>
```

### Différentes tailles

```vue
<template>
  <!-- Extra Small -->
  <AppModal v-model="isOpen" title="Extra Small" size="xs">
    <p>Modal extra petite (320px)</p>
  </AppModal>

  <!-- Small -->
  <AppModal v-model="isOpen" title="Small" size="sm">
    <p>Modal petite (400px)</p>
  </AppModal>

  <!-- Medium (défaut) -->
  <AppModal v-model="isOpen" title="Medium" size="md">
    <p>Modal moyenne (500px)</p>
  </AppModal>

  <!-- Large -->
  <AppModal v-model="isOpen" title="Large" size="lg">
    <p>Modal large (700px)</p>
  </AppModal>

  <!-- Extra Large -->
  <AppModal v-model="isOpen" title="Extra Large" size="xl">
    <p>Modal extra large (900px)</p>
  </AppModal>

  <!-- Full -->
  <AppModal v-model="isOpen" title="Full Screen" size="full">
    <p>Modal plein écran</p>
  </AppModal>
</template>
```

### Différentes positions

```vue
<template>
  <!-- Centre (défaut) -->
  <AppModal v-model="isOpen" title="Centré" position="center">
    <p>Modal au centre de l'écran</p>
  </AppModal>

  <!-- Haut -->
  <AppModal v-model="isOpen" title="En haut" position="top">
    <p>Modal en haut de l'écran (slide down)</p>
  </AppModal>

  <!-- Bas (bottom sheet) -->
  <AppModal v-model="isOpen" title="En bas" position="bottom">
    <p>Modal en bas de l'écran (slide up)</p>
  </AppModal>
</template>
```

### Modal avec formulaire

```vue
<template>
  <AppModal
    v-model="showForm"
    title="Ajouter un utilisateur"
    icon="pi pi-user-plus"
    size="md"
    show-cancel-button
    show-confirm-button
    confirm-text="Enregistrer"
    :loading="saving"
    @confirm="handleSubmit"
    @cancel="resetForm"
  >
    <form @submit.prevent="handleSubmit">
      <AppInput
        v-model="form.name"
        label="Nom"
        placeholder="Entrez le nom"
        required
      />
      <AppInput
        v-model="form.email"
        type="email"
        label="Email"
        placeholder="Entrez l'email"
        required
      />
    </form>
  </AppModal>
</template>

<script setup>
import { ref, reactive } from 'vue'

const showForm = ref(false)
const saving = ref(false)
const form = reactive({
  name: '',
  email: ''
})

const handleSubmit = async () => {
  saving.value = true
  // Simuler une requête API
  await new Promise(resolve => setTimeout(resolve, 1000))
  console.log('Formulaire soumis:', form)
  saving.value = false
  showForm.value = false
  resetForm()
}

const resetForm = () => {
  form.name = ''
  form.email = ''
  showForm.value = false
}
</script>
```

### Modal persistante

```vue
<template>
  <AppModal
    v-model="showPersistent"
    title="Action importante"
    persistent
    show-cancel-button
    show-confirm-button
    @confirm="handleConfirm"
  >
    <p>Cette modal ne peut pas être fermée en cliquant en dehors ou avec Escape.</p>
    <p>Vous devez cliquer sur "Confirmer" ou "Annuler".</p>
  </AppModal>
</template>
```

### Modal sans header ni footer

```vue
<template>
  <AppModal
    v-model="isOpen"
    :show-header="false"
    :show-footer="false"
    size="sm"
    body-padding="xl"
  >
    <div class="text-center">
      <i class="pi pi-check-circle text-6xl text-green-500 mb-4"></i>
      <h2 class="text-2xl font-bold mb-2">Succès !</h2>
      <p class="text-gray-600 mb-6">Votre action a été effectuée avec succès.</p>
      <AppButton text-content="Fermer" @click="isOpen = false" />
    </div>
  </AppModal>
</template>
```

### Header et footer personnalisés

```vue
<template>
  <AppModal v-model="isOpen" size="lg">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
            <i class="pi pi-user text-blue-600"></i>
          </div>
          <div>
            <h3 class="font-semibold text-lg">Profil utilisateur</h3>
            <p class="text-sm text-gray-500">Modifier les informations</p>
          </div>
        </div>
        <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">
          <i class="pi pi-times"></i>
        </button>
      </div>
    </template>

    <p>Contenu de la modal...</p>

    <template #footer>
      <div class="flex justify-between w-full">
        <AppButton variant="danger" text-content="Supprimer" />
        <div class="flex gap-2">
          <AppButton variant="secondary" text-content="Annuler" @click="isOpen = false" />
          <AppButton variant="primary" text-content="Sauvegarder" />
        </div>
      </div>
    </template>
  </AppModal>
</template>
```

### Modal avec contenu scrollable

```vue
<template>
  <AppModal
    v-model="isOpen"
    title="Conditions d'utilisation"
    size="lg"
    show-footer
    show-confirm-button
    confirm-text="J'accepte"
  >
    <div class="space-y-4">
      <p>Lorem ipsum dolor sit amet...</p>
      <!-- Beaucoup de contenu -->
      <p>Contenu très long qui nécessite un scroll...</p>
    </div>
  </AppModal>
</template>
```

### Bottom Sheet mobile

```vue
<template>
  <AppModal
    v-model="showBottomSheet"
    title="Options"
    position="bottom"
    size="md"
    body-padding="lg"
  >
    <div class="space-y-2">
      <button class="w-full text-left p-3 hover:bg-gray-100 rounded">
        <i class="pi pi-share-alt mr-2"></i> Partager
      </button>
      <button class="w-full text-left p-3 hover:bg-gray-100 rounded">
        <i class="pi pi-download mr-2"></i> Télécharger
      </button>
      <button class="w-full text-left p-3 hover:bg-gray-100 rounded text-red-600">
        <i class="pi pi-trash mr-2"></i> Supprimer
      </button>
    </div>
  </AppModal>
</template>
```

### Modal sans backdrop blur

```vue
<template>
  <AppModal
    v-model="isOpen"
    title="Sans blur"
    :backdrop-blur="false"
  >
    <p>Cette modal n'a pas d'effet de flou sur le backdrop.</p>
  </AppModal>
</template>
```

### Modal sans padding dans le body

```vue
<template>
  <AppModal
    v-model="isOpen"
    title="Galerie"
    body-padding="none"
    size="lg"
  >
    <img src="/image.jpg" alt="Image" class="w-full" />
  </AppModal>
</template>
```

## Cas d'usage

### Confirmation de suppression
```vue
<AppModal
  v-model="showDelete"
  title="Supprimer"
  icon="pi pi-exclamation-triangle"
  size="sm"
  show-cancel-button
  show-confirm-button
  confirm-variant="danger"
  @confirm="deleteItem"
/>
```

### Formulaire de création/édition
```vue
<AppModal
  v-model="showForm"
  title="Éditer"
  size="md"
  show-cancel-button
  show-confirm-button
  :loading="saving"
  @confirm="saveForm"
/>
```

### Message de succès
```vue
<AppModal
  v-model="showSuccess"
  :show-header="false"
  :closable="false"
  size="sm"
/>
```

### Lightbox / Galerie d'images
```vue
<AppModal
  v-model="showImage"
  :show-header="false"
  body-padding="none"
  size="xl"
/>
```

### Menu contextuel mobile (bottom sheet)
```vue
<AppModal
  v-model="showMenu"
  position="bottom"
  size="sm"
/>
```

## Notes

- La modal utilise `Teleport` pour être rendue au niveau du `<body>`
- Le scroll du body est bloqué automatiquement quand la modal est ouverte
- Les animations changent selon la position (scale pour center, slide pour top/bottom)
- Sur mobile, la modal prend toute la largeur de l'écran
- Support complet de l'accessibilité avec `role="dialog"` et `aria-modal`
- La touche Escape ferme la modal par défaut (sauf si `closeOnEscape` ou `persistent`)
