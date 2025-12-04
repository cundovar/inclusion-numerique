# AppCard

Composant de carte réutilisable et personnalisable avec support pour les listes, les slots et différents styles.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | `null` | Titre de la carte |
| `icon` | String | `null` | Classe d'icône (FontAwesome, PrimeIcons, etc.) |
| `items` | Array | `[]` | Liste d'items à afficher |
| `variant` | String | `'default'` | Style de la carte: `default`, `glassmorphism`, `outline`, `bordered`, `elevated` |
| `size` | String | `'md'` | Taille: `sm`, `md`, `lg`, `xl` |
| `clickable` | Boolean | `false` | Items cliquables avec flèche |
| `showFooter` | Boolean | `false` | Afficher le footer avec compteur |
| `showTopBorder` | Boolean | `false` | Afficher bordure décorative gradient en haut |
| `footerText` | String | `'items'` | Texte du footer (ex: "cours", "exercices") |
| `footerIcon` | String | `'pi pi-list'` | Icône du footer |
| `hover` | Boolean | `true` | Effet hover (lift + shadow) |
| `padding` | String | `'md'` | Padding: `none`, `sm`, `md`, `lg`, `xl` |
| `rounded` | String | `'lg'` | Arrondi: `none`, `sm`, `md`, `lg`, `xl`, `2xl`, `3xl` |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `item-click` | `{ item, index }` | Émis lors du clic sur un item (si clickable) |
| `click` | Event | Émis lors du clic sur la carte |

## Slots

| Slot | Props | Description |
|------|-------|-------------|
| `header` | - | Remplace tout le header (titre + icône) |
| `default` | - | Contenu principal de la carte |
| `item` | `{ item, index }` | Template pour chaque item de la liste |
| `footer` | - | Remplace le footer |

## Structure des items

```javascript
{
  id: 1,                    // Identifiant unique
  label: 'Mon item',        // ou title, ou name
  icon: 'pi pi-file',       // Icône de l'item (optionnel)
  // ... autres propriétés personnalisées
}
```

## Exemples d'utilisation

### Carte simple avec titre et contenu

```vue
<AppCard title="Ma Carte" icon="pi pi-home">
  <p>Contenu personnalisé de ma carte</p>
</AppCard>
```

### Carte avec liste d'items

```vue
<template>
  <AppCard
    title="Mes Cours"
    icon="fas fa-graduation-cap"
    :items="courses"
    clickable
    showFooter
    footerText="cours"
    footerIcon="pi pi-book"
    @item-click="handleItemClick"
  />
</template>

<script setup>
const courses = [
  { id: 1, label: 'HTML & CSS', icon: 'pi pi-file-code' },
  { id: 2, label: 'JavaScript', icon: 'pi pi-code' },
  { id: 3, label: 'Vue.js', icon: 'pi pi-sitemap' }
]

const handleItemClick = ({ item, index }) => {
  console.log('Clicked:', item, 'at index:', index)
}
</script>
```

### Carte avec slot personnalisé pour les items

```vue
<AppCard
  title="Exercices"
  :items="exercises"
  clickable
>
  <template #item="{ item, index }">
    <div class="custom-item">
      <span class="number">{{ index + 1 }}.</span>
      <h4>{{ item.title }}</h4>
      <span class="badge">{{ item.difficulty }}</span>
    </div>
  </template>
</AppCard>
```

### Carte style glassmorphism

```vue
<AppCard
  title="Dashboard"
  icon="pi pi-chart-line"
  variant="glassmorphism"
  :items="stats"
  showTopBorder
  rounded="2xl"
/>
```

### Carte outline avec header personnalisé

```vue
<AppCard variant="outline">
  <template #header>
    <div class="custom-header">
      <img src="/logo.png" alt="Logo" />
      <h2>Titre personnalisé</h2>
    </div>
  </template>

  <p>Contenu de la carte</p>
</AppCard>
```

### Carte bordered (style CourseCard)

```vue
<AppCard
  title="JavaScript"
  icon="fas fa-code"
  variant="bordered"
  :items="lessons"
  clickable
  showFooter
  footerText="leçons"
  size="lg"
/>
```

## Variantes disponibles

### default
Carte blanche classique avec bordure et ombre légère

### glassmorphism
Effet verre dépoli avec transparence et flou (style moderne)

### outline
Bordure épaisse sans fond (style minimaliste)

### bordered
Bordure gauche colorée (style CourseCard actuel)

### elevated
Ombre prononcée sans bordure (style Material Design)

## Notes

- Le composant utilise les variables CSS `--primary-color` et `--color-text` si elles sont définies
- Les items doivent avoir au minimum une propriété `label`, `title` ou `name`
- Le slot `item` permet une personnalisation totale de l'apparence de chaque item
- La hauteur minimale s'adapte selon la prop `size`
