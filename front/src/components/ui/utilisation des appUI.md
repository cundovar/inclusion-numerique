# Documentation des Composants UI

Cette documentation décrit l'utilisation des composants UI réutilisables de l'application.

---

## Table des matières

1. [AppButton](#appbutton)
2. [AppInput](#appinput)
3. [AppSelect](#appselect)
4. [AppCard](#appcard)
5. [AppModal](#appmodal)
6. [AppAlert](#appalert)
7. [AppBadge](#appbadge)
8. [NiveauFilter](#niveaufilter)
9. [SafeHtml](#safehtml)

---

## AppButton

Bouton réutilisable avec plusieurs variantes et états.

### Import

```javascript
import AppButton from '@/components/ui/AppButton.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `type` | `String` | `'button'` | Type HTML : `button`, `submit`, `reset` |
| `variant` | `String` | `'primary'` | Style : `primary`, `secondary`, `danger`, `success`, `warning`, `ghost`, `outline`, `profile`, `auth`, `favorite`, `unfavorite`, `neutral` |
| `size` | `String` | `'md'` | Taille : `xs`, `sm`, `md`, `lg`, `xl` |
| `icon` | `String` | `null` | Classe d'icône (PrimeIcons ou FontAwesome) |
| `iconPosition` | `String` | `'left'` | Position de l'icône : `left`, `right` |
| `textContent` | `String` | `null` | Texte du bouton |
| `disabled` | `Boolean` | `false` | État désactivé |
| `loading` | `Boolean` | `false` | Affiche un spinner |
| `fullWidth` | `Boolean` | `false` | Pleine largeur |
| `rounded` | `String` | `'md'` | Arrondi : `none`, `sm`, `md`, `lg`, `full` |
| `bgHover` | `String` | `''` | Classes Tailwind pour le hover |
| `hoverBgColor` | `String` | `''` | Couleur hex pour le hover |

### Events

| Event | Description |
|-------|-------------|
| `@click` | Émis au clic |

### Exemples

```vue
<!-- Bouton simple -->
<AppButton text-content="Cliquer" />

<!-- Avec icône -->
<AppButton
  variant="success"
  icon="pi pi-check"
  text-content="Valider"
/>

<!-- État loading -->
<AppButton
  :loading="isLoading"
  text-content="Enregistrer"
/>

<!-- Outline avec taille -->
<AppButton
  variant="outline"
  size="lg"
  text-content="En savoir plus"
/>

<!-- Bouton danger pleine largeur -->
<AppButton
  variant="danger"
  full-width
  text-content="Supprimer"
/>
```

---

## AppInput

Champ de saisie polyvalent (texte, password, textarea, select).

### Import

```javascript
import AppInput from '@/components/ui/AppInput.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `v-model` | `String/Number/Array` | `''` | Valeur liée |
| `type` | `String` | `'text'` | Type : `text`, `password`, `email`, `number`, `textarea`, `select` |
| `label` | `String` | `null` | Label du champ |
| `placeholder` | `String` | `''` | Placeholder |
| `size` | `String` | `'default'` | Taille : `large`, `default`, `small` |
| `disabled` | `Boolean` | `false` | Désactivé |
| `readonly` | `Boolean` | `false` | Lecture seule |
| `required` | `Boolean` | `false` | Affiche un astérisque rouge |
| `clearable` | `Boolean` | `false` | Bouton pour effacer |
| `maxlength` | `Number` | `null` | Longueur maximale |
| `prefixIcon` | `String` | `null` | Icône avant |
| `suffixIcon` | `String` | `null` | Icône après |
| `error` | `String` | `null` | Message d'erreur |
| `helpText` | `String` | `null` | Texte d'aide |
| `options` | `Array` | `[]` | Options pour select |
| `multiple` | `Boolean` | `false` | Select multiple |
| `filterable` | `Boolean` | `false` | Select filtrable |
| `rows` | `Number` | `3` | Lignes pour textarea |
| `autosize` | `Boolean/Object` | `false` | Auto-redimensionnement textarea |
| `showWordLimit` | `Boolean` | `false` | Affiche le compteur de caractères |

### Events

| Event | Description |
|-------|-------------|
| `@update:modelValue` | Mise à jour de la valeur |
| `@change` | Changement de valeur |
| `@input` | Saisie en cours |
| `@blur` | Perte de focus |
| `@focus` | Obtention du focus |

### Exemples

```vue
<!-- Input texte avec label -->
<AppInput
  v-model="email"
  type="email"
  label="Email"
  placeholder="votre@email.com"
  required
/>

<!-- Password -->
<AppInput
  v-model="password"
  type="password"
  label="Mot de passe"
/>

<!-- Textarea avec compteur -->
<AppInput
  v-model="description"
  type="textarea"
  label="Description"
  :maxlength="500"
  show-word-limit
/>

<!-- Input avec erreur -->
<AppInput
  v-model="username"
  label="Nom d'utilisateur"
  error="Ce nom est déjà pris"
/>

<!-- Select -->
<AppInput
  v-model="country"
  type="select"
  label="Pays"
  :options="[
    { label: 'France', value: 'FR' },
    { label: 'Belgique', value: 'BE' }
  ]"
/>
```

---

## AppSelect

Sélecteur avancé avec support cascader.

### Import

```javascript
import AppSelect from '@/components/ui/AppSelect.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `v-model` | `String/Number/Array/Boolean` | `null` | Valeur sélectionnée |
| `options` | `Array` | `[]` | Options disponibles |
| `optionLabel` | `String` | `'label'` | Clé pour le label |
| `optionValue` | `String` | `'value'` | Clé pour la valeur |
| `placeholder` | `String` | `'Sélectionnez'` | Placeholder |
| `label` | `String` | `null` | Label du champ |
| `size` | `String` | `'default'` | Taille : `large`, `default`, `small` |
| `multiple` | `Boolean` | `false` | Sélection multiple |
| `filterable` | `Boolean` | `false` | Filtrage des options |
| `clearable` | `Boolean` | `false` | Bouton effacer |
| `disabled` | `Boolean` | `false` | Désactivé |
| `required` | `Boolean` | `false` | Requis |
| `cascader` | `Boolean` | `false` | Mode cascader |
| `cascaderProps` | `Object` | `{value, label, children}` | Configuration cascader |
| `collapseTags` | `Boolean` | `false` | Réduire les tags (multiple) |
| `showAllLevels` | `Boolean` | `true` | Afficher tous les niveaux (cascader) |
| `error` | `String` | `null` | Message d'erreur |
| `helpText` | `String` | `null` | Texte d'aide |

### Events

| Event | Description |
|-------|-------------|
| `@update:modelValue` | Mise à jour |
| `@change` | Changement |
| `@blur` | Perte de focus |
| `@focus` | Obtention du focus |

### Exemples

```vue
<!-- Select simple -->
<AppSelect
  v-model="niveau"
  :options="niveaux"
  option-label="name"
  option-value="id"
  placeholder="Choisir un niveau"
/>

<!-- Select multiple filtrable -->
<AppSelect
  v-model="categories"
  :options="categoryList"
  multiple
  filterable
  clearable
  placeholder="Sélectionner les catégories"
/>

<!-- Cascader -->
<AppSelect
  v-model="location"
  :options="locationTree"
  cascader
  placeholder="Région > Ville"
/>
```

---

## AppCard

Carte pour afficher des listes d'items.

### Import

```javascript
import AppCard from '@/components/ui/AppCard.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `title` | `String` | **requis** | Titre de la carte |
| `icon` | `String` | `'fas fa-code'` | Icône du header |
| `items` | `Array` | `[]` | Liste d'items |
| `clickable` | `Boolean` | `false` | Items cliquables |
| `showFooter` | `Boolean` | `false` | Afficher le footer |
| `showTopBorder` | `Boolean` | `false` | Bordure décorative en haut |
| `footerText` | `String` | `'items'` | Texte du footer |
| `footerIcon` | `String` | `'fas fa-list'` | Icône du footer |
| `borderClass` | `String` | `'border-l-pink-500'` | Classe bordure gauche |

### Slots

| Slot | Description |
|------|-------------|
| `default` | Contenu principal |
| `header` | Header personnalisé |
| `item` | Template pour chaque item (props: `item`, `index`) |
| `footer` | Footer personnalisé |

### Events

| Event | Description |
|-------|-------------|
| `@item-click` | Clic sur un item |

### Exemples

```vue
<!-- Carte simple -->
<AppCard
  title="Mes cours"
  icon="fas fa-book"
  :items="courses"
  show-footer
  footer-text="cours"
/>

<!-- Avec slot item personnalisé -->
<AppCard title="Utilisateurs" :items="users">
  <template #item="{ item }">
    <div class="flex items-center gap-2">
      <img :src="item.avatar" class="w-8 h-8 rounded-full" />
      <span>{{ item.name }}</span>
    </div>
  </template>
</AppCard>
```

---

## AppModal

Modal (popup) avec gestion complète.

### Import

```javascript
import AppModal from '@/components/ui/AppModal.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `v-model` | `Boolean` | `false` | Visibilité |
| `title` | `String` | `''` | Titre |
| `icon` | `String` | `null` | Icône du titre |
| `size` | `String` | `'md'` | Taille : `xs`, `sm`, `md`, `lg`, `xl`, `full` |
| `position` | `String` | `'center'` | Position : `center`, `top`, `bottom` |
| `closable` | `Boolean` | `true` | Bouton fermer visible |
| `closeOnBackdrop` | `Boolean` | `true` | Ferme au clic sur le fond |
| `closeOnEscape` | `Boolean` | `true` | Ferme avec Escape |
| `persistent` | `Boolean` | `false` | Empêche la fermeture |
| `showHeader` | `Boolean` | `true` | Afficher le header |
| `showFooter` | `Boolean` | `false` | Afficher le footer |
| `bodyPadding` | `String` | `'md'` | Padding : `none`, `sm`, `md`, `lg`, `xl` |
| `backdropBlur` | `Boolean` | `true` | Flou sur le fond |
| `showCancelButton` | `Boolean` | `false` | Bouton annuler |
| `showConfirmButton` | `Boolean` | `false` | Bouton confirmer |
| `cancelText` | `String` | `'Annuler'` | Texte annuler |
| `confirmText` | `String` | `'Confirmer'` | Texte confirmer |
| `confirmVariant` | `String` | `'primary'` | Variante du bouton confirmer |
| `loading` | `Boolean` | `false` | État loading du bouton confirmer |

### Slots

| Slot | Description |
|------|-------------|
| `default` | Contenu du body |
| `header` | Header personnalisé |
| `footer` | Footer personnalisé |

### Events

| Event | Description |
|-------|-------------|
| `@update:modelValue` | Changement de visibilité |
| `@open` | Ouverture |
| `@close` | Fermeture |
| `@confirm` | Clic sur confirmer |
| `@cancel` | Clic sur annuler |

### Exemples

```vue
<!-- Modal simple -->
<AppModal v-model="showModal" title="Confirmation">
  <p>Êtes-vous sûr de vouloir continuer ?</p>
</AppModal>

<!-- Modal avec footer -->
<AppModal
  v-model="showDelete"
  title="Supprimer"
  icon="pi pi-trash"
  show-footer
  show-cancel-button
  show-confirm-button
  confirm-text="Supprimer"
  confirm-variant="danger"
  @confirm="handleDelete"
>
  <p>Cette action est irréversible.</p>
</AppModal>

<!-- Modal persistante (ne peut être fermée qu'avec le bouton) -->
<AppModal
  v-model="showForced"
  title="Action requise"
  persistent
  :closable="false"
>
  <p>Veuillez compléter cette action.</p>
</AppModal>
```

---

## AppAlert

Alertes et notifications.

### Import

```javascript
import AppAlert from '@/components/ui/AppAlert.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `v-model` | `Boolean` | `false` | Visibilité |
| `show` | `Boolean` | `false` | Alternative à v-model |
| `type` | `String` | `'info'` | Type : `success`, `error`, `warning`, `info` |
| `variant` | `String` | `'solid'` | Style : `solid`, `light`, `outline` |
| `title` | `String` | `null` | Titre de l'alerte |
| `message` | `String` | `null` | Message |
| `dismissible` | `Boolean` | `true` | Peut être fermée |

### Slots

| Slot | Description |
|------|-------------|
| `default` | Contenu personnalisé |

### Events

| Event | Description |
|-------|-------------|
| `@update:modelValue` | Changement de visibilité |
| `@close` | Fermeture |

### Exemples

```vue
<!-- Alerte succès -->
<AppAlert
  type="success"
  title="Succès !"
  message="Votre modification a été enregistrée."
  show
/>

<!-- Alerte erreur avec v-model -->
<AppAlert
  v-model="showError"
  type="error"
  title="Erreur"
  message="Une erreur est survenue."
/>

<!-- Alerte light -->
<AppAlert
  type="warning"
  variant="light"
  message="Attention, cette action est risquée."
  show
/>

<!-- Alerte non fermable -->
<AppAlert
  type="info"
  :dismissible="false"
  message="Information importante."
  show
/>
```

---

## AppBadge

Badge/Tag pour afficher des statuts ou labels.

### Import

```javascript
import AppBadge from '@/components/ui/AppBadge.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `variant` | `String` | `'default'` | Couleur : `default`, `primary`, `secondary`, `success`, `warning`, `danger`, `info`, `dark` |
| `size` | `String` | `'md'` | Taille : `xs`, `sm`, `md`, `lg`, `xl` |
| `icon` | `String` | `null` | Icône |
| `dot` | `Boolean` | `false` | Affiche un point indicateur |
| `removable` | `Boolean` | `false` | Bouton supprimer |
| `rounded` | `String` | `'md'` | Arrondi : `none`, `sm`, `md`, `lg`, `full` |
| `clickable` | `Boolean` | `false` | Cliquable |
| `outline` | `Boolean` | `false` | Style outline |

### Slots

| Slot | Description |
|------|-------------|
| `default` | Contenu du badge |
| `icon` | Icône personnalisée |

### Events

| Event | Description |
|-------|-------------|
| `@click` | Clic (si clickable) |
| `@remove` | Clic sur supprimer |

### Exemples

```vue
<!-- Badge simple -->
<AppBadge variant="success">Actif</AppBadge>

<!-- Badge avec icône -->
<AppBadge variant="primary" icon="fas fa-star">
  Premium
</AppBadge>

<!-- Badge supprimable -->
<AppBadge
  variant="info"
  removable
  @remove="handleRemove"
>
  Tag supprimable
</AppBadge>

<!-- Badge outline avec dot -->
<AppBadge variant="warning" outline dot>
  En attente
</AppBadge>

<!-- Badge pill (full rounded) -->
<AppBadge variant="danger" rounded="full">
  3 erreurs
</AppBadge>
```

---

## NiveauFilter

Filtre par niveau de cours avec boutons.

### Import

```javascript
import NiveauFilter from '@/components/ui/NiveauFilter.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `v-model` | `Object` | `null` | Niveau sélectionné (objet avec `name` et `ordre`) |
| `niveaux` | `Array` | **requis** | Liste des niveaux (objets avec `id`, `name` et `ordre`) |

### Events

| Event | Description |
|-------|-------------|
| `@update:modelValue` | Changement de niveau |

### Exemple

```vue
<script setup>
import { useNiveauStore } from '@/stores/niveauCoursStore'
import { storeToRefs } from 'pinia'

const niveauStore = useNiveauStore()
const { niveaux, selectedNiveau } = storeToRefs(niveauStore)
</script>

<template>
  <NiveauFilter
    v-model="selectedNiveau"
    :niveaux="niveaux"
  />
</template>
```

---

## Système de filtrage par niveau (Global)

Le filtrage par niveau est un système global qui permet de filtrer les catégories/cours selon leur niveau de difficulté dans toute l'application.

### Principe de fonctionnement

Le filtrage se fait par **ordre** (et non par nom) :
- Quand on sélectionne un niveau avec `ordre = 2`, on affiche les catégories avec `ordre <= 2` (donc niveaux 1 et 2)
- Cela permet d'afficher tous les niveaux inférieurs ou égaux au niveau sélectionné

### Structure des données

```javascript
// selectedNiveau est un OBJET (pas une string)
selectedNiveau = {
  id: 2,
  name: "Intermédiaire",
  ordre: 2
}

// Catégorie avec niveauCours
category = {
  name: "JavaScript",
  niveauCours: {
    id: 1,
    name: "Débutant",
    ordre: 1
  }
}
```

### Store Pinia (niveauCoursStore)

```javascript
import { useNiveauStore } from '@/stores/niveauCoursStore'
import { storeToRefs } from 'pinia'

const niveauStore = useNiveauStore()
const { niveaux, selectedNiveau } = storeToRefs(niveauStore)

// selectedNiveau est un objet { name, ordre } ou null
// niveaux est un tableau d'objets { id, name, ordre }
```

### Logique de filtrage (utilisée dans App.vue et HomePage.vue)

```javascript
// Filtrer les catégories par niveau
if (selectedNiveau.value) {
  categories = categories.filter((cat) => {
    const catOrdre = cat.niveauCours?.ordre
    const selectedOrdre = selectedNiveau.value.ordre
    const hasNoNiveau = !cat.niveauCours?.name

    // Garder la catégorie si:
    // 1. Son ordre <= ordre sélectionné (ex: ordre 1 et 2 si on sélectionne niveau ordre 2)
    // 2. OU si la catégorie n'a pas de niveau défini
    return catOrdre <= selectedOrdre || hasNoNiveau
  })
}
```

### Configuration Backend (Symfony)

L'entité `NiveauCours` doit exposer la propriété `ordre` dans le groupe de sérialisation approprié :

```php
// src/Entity/NiveauCours.php
#[ORM\Column(type: 'integer', options: ['default' => 0])]
#[Groups(['niveau_cours:read', 'page_content:read'])]
private ?int $ordre = null;
```

### Composants utilisant ce système

| Composant | Rôle |
|-----------|------|
| `AppHeader.vue` | Sélecteur de niveau (select dans le header) |
| `App.vue` | Filtre `catsMenuGauche` pour le menu de navigation |
| `HomePage.vue` | Filtre `filteredMenusByCategory` pour la liste des catégories |

### Persistance

Le niveau sélectionné est persisté dans le localStorage (clé: `filter-niveau`) via `pinia-plugin-persistedstate`

---

## SafeHtml

Affichage sécurisé de contenu HTML (protection XSS).

### Import

```javascript
import SafeHtml from '@/components/ui/SafeHtml.vue'
```

### Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `html` | `String` | `''` | Contenu HTML à afficher |
| `class` | `String` | `''` | Classes CSS |

### Tags autorisés

`h1-h6`, `p`, `br`, `hr`, `strong`, `em`, `u`, `s`, `mark`, `small`, `ul`, `ol`, `li`, `blockquote`, `pre`, `code`, `span`, `div`, `main`, `table`, `thead`, `tbody`, `tr`, `th`, `td`, `a`, `details`, `summary`

### Attributs autorisés

`href`, `title`, `target`, `rel`, `class`

### Exemple

```vue
<!-- Affichage sécurisé de contenu de l'API -->
<SafeHtml :html="article.content" class="prose" />

<!-- Le contenu malveillant est automatiquement nettoyé -->
<SafeHtml :html="userInput" />
<!-- Les scripts, iframes, onclick, etc. sont supprimés -->
```

---

## Bonnes pratiques

1. **Toujours utiliser `SafeHtml`** pour afficher du contenu HTML provenant de l'utilisateur ou de l'API
2. **Préférer `AppButton`** aux balises `<button>` natives pour une cohérence visuelle
3. **Utiliser les variantes** appropriées selon le contexte (danger pour supprimer, success pour valider, etc.)
4. **Gérer les états loading** sur les boutons lors des appels API
5. **Valider les formulaires** côté client avec les props `error` et `required`
