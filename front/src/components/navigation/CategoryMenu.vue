<template>
  <!-- Boucle sur les SuperMenus -->
  <div v-for="(superMenu, superMenuName) in categoriesBySuperMenu" :key="superMenuName" class="border-b border-gray-300">
    <details class="group">
      <summary class="cursor-pointer p-2 bg-blue-100 hover:bg-blue-200 font-bold uppercase text-gray-500">
        {{ superMenuName }}
      </summary>

      <!-- Boucle sur les Categories de ce SuperMenu -->
      <div class="">
        <CategoryMenuItem
          v-for="cat in superMenu"
          :key="cat.id"
          :cat="cat"
          :menusByCategory="menusByCategory"
          :hoveredCategory="hoveredCategory"
          :clickMenu="clickMenu"
          :isXlOuPlus="isXlOuPlus"
          :islgOuPlus="islgOuPlus"
          :isOpenMenuGauche="isOpenMenuGauche"
          @openMenu="$emit('openMenu', $event)"
          @hoverCategory="$emit('hoverCategory', $event)"
          @closeMenu="$emit('closeMenu')"
        />
      </div>
    </details>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import CategoryMenuItem from './CategoryMenuItem.vue';

const props = defineProps({
  cats: {
    type: Array,
    required: true
  },
  menusByCategory: {
    type: Object,
    required: true
  },
  hoveredCategory: {
    type: String,
    default: null
  },
  clickMenu: {
    type: String,
    default: null
  },
  isXlOuPlus: {
    type: Boolean,
    default: false
  },
  islgOuPlus: {
    type: Boolean,
    default: false
  },
  isOpenMenuGauche: {
    type: Boolean,
    default: false
  }
});

// Grouper les catégories par SuperMenu
const categoriesBySuperMenu = computed(() => {
  const grouped = {};

  props.cats.forEach(cat => {
    const superMenuName = cat.superMenu?.name || 'Autres';

    if (!grouped[superMenuName]) {
      grouped[superMenuName] = [];
    }

    grouped[superMenuName].push(cat);
  });

  return grouped;
});

defineEmits(['openMenu', 'hoverCategory', 'closeMenu']);
</script>
