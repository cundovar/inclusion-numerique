<template>
  <div
    v-if="sorted.length"
    class="niveau-legend"
  >
    <!-- Sens des marches affichées seules dans les listes de cours : écrit en clair, toujours visible -->
    <span class="niveau-legend-title">Niveaux :</span>
    <ul class="niveau-legend-list">
      <li
        v-for="niveau in sorted"
        :key="niveau.id"
      >
        <NiveauBadge
          :niveau="niveau"
          :niveaux="niveaux"
          variant="legend"
        />
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import NiveauBadge from './NiveauBadge.vue'
import { sortNiveaux } from '@/utils/niveaux'

const props = defineProps({
  // Tous les niveaux du site
  niveaux: {
    type: Array,
    default: () => []
  }
})

const sorted = computed(() => sortNiveaux(props.niveaux).filter((niveau) => niveau?.name))
</script>

<style scoped>
.niveau-legend {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.5rem 1rem;
  margin-top: 1rem;
  font-size: 1rem;
  color: #1f2937;
}

.niveau-legend-title {
  font-weight: 600;
}

.niveau-legend-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.5rem 1.25rem;
  margin: 0;
  padding: 0;
  list-style: none;
}
</style>
