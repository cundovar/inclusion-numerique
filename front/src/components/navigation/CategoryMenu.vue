<template>
  <CategoryMenuItem
    v-for="cat in cats"
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
</template>

<script setup>
import CategoryMenuItem from './CategoryMenuItem.vue';

defineProps({
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

defineEmits(['openMenu', 'hoverCategory', 'closeMenu']);
</script>
