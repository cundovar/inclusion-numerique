# AppBadge

Composant de badge/tag réutilisable et personnalisable pour afficher des labels, statuts, catégories, etc.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | String | `'default'` | Style du badge: `default`, `primary`, `secondary`, `success`, `warning`, `danger`, `info`, `dark` |
| `size` | String | `'md'` | Taille: `xs`, `sm`, `md`, `lg`, `xl` |
| `icon` | String | `null` | Classe d'icône (FontAwesome, PrimeIcons, etc.) |
| `dot` | Boolean | `false` | Afficher un point indicateur avant le contenu |
| `removable` | Boolean | `false` | Afficher un bouton de suppression |
| `rounded` | String | `'md'` | Arrondi: `none`, `sm`, `md`, `lg`, `full` |
| `clickable` | Boolean | `false` | Badge cliquable avec effet hover |
| `outline` | Boolean | `false` | Variante outline (bordure sans fond) |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `click` | Event | Émis lors du clic sur le badge (si clickable) |
| `remove` | Event | Émis lors du clic sur le bouton de suppression |

## Slots

| Slot | Props | Description |
|------|-------|-------------|
| `default` | - | Contenu du badge |
| `icon` | - | Icône personnalisée (alternative à la prop icon) |

## Exemples d'utilisation

### Badge simple

```vue
<AppBadge>Default</AppBadge>
<AppBadge variant="primary">Primary</AppBadge>
<AppBadge variant="success">Success</AppBadge>
<AppBadge variant="warning">Warning</AppBadge>
<AppBadge variant="danger">Danger</AppBadge>
```

### Avec icône

```vue
<AppBadge variant="primary" icon="fas fa-star">
  Featured
</AppBadge>

<AppBadge variant="success" icon="fas fa-check">
  Completed
</AppBadge>

<AppBadge variant="info" icon="pi pi-info-circle">
  Information
</AppBadge>
```

### Avec dot indicator

```vue
<AppBadge variant="success" dot>
  En ligne
</AppBadge>

<AppBadge variant="danger" dot>
  Hors ligne
</AppBadge>

<AppBadge variant="warning" dot>
  Occupé
</AppBadge>
```

### Tailles différentes

```vue
<AppBadge size="xs" variant="primary">Extra Small</AppBadge>
<AppBadge size="sm" variant="primary">Small</AppBadge>
<AppBadge size="md" variant="primary">Medium</AppBadge>
<AppBadge size="lg" variant="primary">Large</AppBadge>
<AppBadge size="xl" variant="primary">Extra Large</AppBadge>
```

### Badge supprimable

```vue
<template>
  <AppBadge
    v-for="tag in tags"
    :key="tag.id"
    variant="primary"
    removable
    @remove="removeTag(tag.id)"
  >
    {{ tag.name }}
  </AppBadge>
</template>

<script setup>
import { ref } from 'vue'

const tags = ref([
  { id: 1, name: 'Vue.js' },
  { id: 2, name: 'JavaScript' },
  { id: 3, name: 'CSS' }
])

const removeTag = (id) => {
  tags.value = tags.value.filter(t => t.id !== id)
}
</script>
```

### Badge cliquable

```vue
<AppBadge
  variant="info"
  clickable
  @click="handleClick"
>
  Cliquez-moi
</AppBadge>

<script setup>
const handleClick = () => {
  console.log('Badge clicked!')
}
</script>
```

### Variante outline

```vue
<AppBadge variant="primary" outline>Primary Outline</AppBadge>
<AppBadge variant="success" outline>Success Outline</AppBadge>
<AppBadge variant="danger" outline>Danger Outline</AppBadge>
```

### Badge arrondi complet (pill)

```vue
<AppBadge variant="primary" rounded="full">
  Pill Badge
</AppBadge>

<AppBadge variant="success" rounded="full" icon="fas fa-check">
  Verified
</AppBadge>
```

### Badge avec icône personnalisée (slot)

```vue
<AppBadge variant="warning">
  <template #icon>
    <svg width="12" height="12" viewBox="0 0 24 24">
      <path d="..." fill="currentColor"/>
    </svg>
  </template>
  Custom Icon
</AppBadge>
```

### Cas d'usage réels

#### Catégories

```vue
<AppBadge
  v-for="category in categories"
  :key="category.id"
  variant="primary"
  size="sm"
  rounded="full"
>
  {{ category.name }}
</AppBadge>
```

#### Statut utilisateur

```vue
<AppBadge
  :variant="user.isOnline ? 'success' : 'default'"
  dot
  size="sm"
>
  {{ user.isOnline ? 'En ligne' : 'Hors ligne' }}
</AppBadge>
```

#### Tags de code

```vue
<AppBadge variant="dark" size="xs" rounded="sm">
  HTML
</AppBadge>
<AppBadge variant="warning" size="xs" rounded="sm">
  JavaScript
</AppBadge>
<AppBadge variant="success" size="xs" rounded="sm">
  CSS
</AppBadge>
```

#### Compteur de notifications

```vue
<AppBadge variant="danger" size="xs" rounded="full">
  {{ notificationCount }}
</AppBadge>
```

## Variantes disponibles

### default
Badge gris neutre pour usage général

### primary
Badge bleu pour éléments importants ou actions principales

### secondary
Badge gris foncé pour éléments secondaires

### success
Badge vert pour statuts positifs, validations, succès

### warning
Badge orange pour avertissements, éléments en attente

### danger
Badge rouge pour erreurs, suppressions, alertes

### info
Badge cyan pour informations, tips, aide

### dark
Badge noir pour contraste élevé, code, tags techniques

## Notes

- Le badge s'adapte automatiquement à son contenu
- Les icônes utilisent `currentColor` et s'adaptent à la couleur du badge
- La variante `outline` utilise des bordures colorées sans fond
- Le bouton de suppression apparaît uniquement si `removable` est `true`
- Le badge devient cliquable automatiquement si `removable` ou `clickable` est `true`
- Compatible avec FontAwesome et PrimeIcons pour les icônes
