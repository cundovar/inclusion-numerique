# AppButton - Composant de bouton réutilisable

## Description
Composant de bouton modulable et réutilisable avec support des icônes, états de chargement, et différentes variantes de style.

## Props

### `type` (String)
Type HTML du bouton
- **Options**: `'button'`, `'submit'`, `'reset'`
- **Défaut**: `'button'`

### `variant` (String)
Variante de style du bouton
- **Options**:
  - `'primary'` - Bleu, pour les actions principales
  - `'secondary'` - Gris, pour les actions secondaires
  - `'danger'` - Rouge, pour les actions destructives
  - `'success'` - Vert, pour les confirmations
  - `'warning'` - Jaune, pour les avertissements
  - `'ghost'` - Transparent, pour les actions discrètes
  - `'outline'` - Contour bleu, pour les actions alternatives
  - `'profile'` - Ambre, pour le bouton de profil utilisateur
  - `'auth'` - Rose, pour les boutons de connexion/déconnexion
  - `'favorite'` - Rouge, pour les éléments en favoris
  - `'unfavorite'` - Gris clair, pour ajouter aux favoris
- **Défaut**: `'primary'`

### `size` (String)
Taille du bouton
- **Options**: `'xs'`, `'sm'`, `'md'`, `'lg'`, `'xl'`
- **Défaut**: `'md'`

### `icon` (String)
Classe d'icône PrimeIcons (ex: `'pi-save'`, `'pi-trash'`, etc.)
- **Défaut**: `null`

### `iconPosition` (String)
Position de l'icône
- **Options**: `'left'`, `'right'`
- **Défaut**: `'left'`

### `textContent` (String)
Texte du bouton
- **Défaut**: `null`

### `disabled` (Boolean)
Désactiver le bouton
- **Défaut**: `false`

### `loading` (Boolean)
Afficher un spinner de chargement
- **Défaut**: `false`

### `fullWidth` (Boolean)
Bouton pleine largeur
- **Défaut**: `false`

### `rounded` (String)
Niveau d'arrondi des coins
- **Options**: `'none'`, `'sm'`, `'md'`, `'lg'`, `'full'`
- **Défaut**: `'md'`

## Events

### `@click`
Émis lors du clic sur le bouton

## Exemples d'utilisation

### Bouton simple
```vue
<AppButton
  text-content="Cliquer ici"
  @click="handleClick"
/>
```

### Bouton avec icône
```vue
<AppButton
  variant="primary"
  icon="pi-save"
  text-content="Enregistrer"
  @click="save"
/>
```

### Bouton de chargement
```vue
<AppButton
  :loading="isSaving"
  icon="pi-save"
  :text-content="isSaving ? 'Enregistrement...' : 'Enregistrer'"
  @click="save"
/>
```

### Bouton danger
```vue
<AppButton
  variant="danger"
  icon="pi-trash"
  text-content="Supprimer"
  @click="deleteItem"
/>
```

### Bouton avec slot personnalisé
```vue
<AppButton variant="primary" @click="handleClick">
  <i class="pi pi-star mr-2"></i>
  <span>Contenu personnalisé</span>
</AppButton>
```

### Bouton désactivé
```vue
<AppButton
  text-content="Indisponible"
  :disabled="true"
/>
```

### Bouton pleine largeur
```vue
<AppButton
  text-content="Soumettre"
  type="submit"
  :full-width="true"
/>
```

### Différentes tailles
```vue
<!-- Extra petit -->
<AppButton size="xs" text-content="XS" />

<!-- Petit -->
<AppButton size="sm" text-content="Small" />

<!-- Moyen (défaut) -->
<AppButton size="md" text-content="Medium" />

<!-- Grand -->
<AppButton size="lg" text-content="Large" />

<!-- Extra grand -->
<AppButton size="xl" text-content="XL" />
```

### Toutes les variantes
```vue
<!-- Primary -->
<AppButton variant="primary" text-content="Primary" />

<!-- Secondary -->
<AppButton variant="secondary" text-content="Secondary" />

<!-- Danger -->
<AppButton variant="danger" text-content="Danger" />

<!-- Success -->
<AppButton variant="success" text-content="Success" />

<!-- Warning -->
<AppButton variant="warning" text-content="Warning" />

<!-- Ghost -->
<AppButton variant="ghost" text-content="Ghost" />

<!-- Outline -->
<AppButton variant="outline" text-content="Outline" />

<!-- Profile -->
<AppButton variant="profile" icon="pi-user" text-content="Profil" />

<!-- Auth (Connexion/Déconnexion) -->
<AppButton variant="auth" icon="pi-sign-in" text-content="Connexion" />
<AppButton variant="auth" icon="pi-sign-out" text-content="Déconnexion" />

<!-- Favoris -->
<AppButton variant="unfavorite" icon="pi-heart" text-content="Ajouter aux favoris" />
<AppButton variant="favorite" icon="pi-heart-fill" text-content="Supprimer des favoris" />
```

### Bouton avec icône à droite
```vue
<AppButton
  icon="pi-arrow-right"
  icon-position="right"
  text-content="Suivant"
/>
```

### Bouton arrondi complet (rond)
```vue
<AppButton
  icon="pi-plus"
  rounded="full"
  size="lg"
/>
```

## Utilisation dans vos composants

```vue
<script setup>
import AppButton from '@/components/commun/button/AppButton.vue'

const handleSave = () => {
  console.log('Sauvegarde en cours...')
}
</script>

<template>
  <AppButton
    variant="primary"
    icon="pi-save"
    text-content="Enregistrer"
    @click="handleSave"
  />
</template>
```
