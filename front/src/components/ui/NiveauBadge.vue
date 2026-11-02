<template>
  <span
    v-if="place"
    class="niveau-badge"
    :class="[`niveau-badge--${variant}`, variant === 'badge' ? `niveau-badge--${Math.min(place.rang, 3)}` : '']"
    :title="variant === 'icon' ? place.nom : undefined"
  >
    <!-- Niveau du cours : marches, nom écrit et phrase complète pour les lecteurs d'écran (pas la couleur seule).
         En variante « icon », seules les marches se voient : la légende de la page donne leur sens. -->
    <span
      class="niveau-steps"
      aria-hidden="true"
    >
      <span
        v-for="step in place.total"
        :key="step"
        class="niveau-step"
        :class="{ 'niveau-step--on': step <= place.rang }"
      />
    </span>
    <span
      v-if="variant !== 'icon'"
      aria-hidden="true"
    >{{ place.nom }}</span>
    <span class="sr-only">Niveau {{ place.rang }} sur {{ place.total }} : {{ place.nom }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { niveauRank } from '@/utils/niveaux'

const props = defineProps({
  // Niveau du cours (niveauCours de l'API)
  niveau: {
    type: Object,
    default: null
  },
  // Tous les niveaux, pour situer celui du cours (1 sur 3…)
  niveaux: {
    type: Array,
    default: () => []
  },
  // badge : pastille complète (page du cours) ; icon : marches seules (listes) ; legend : marches et nom, sans fond
  variant: {
    type: String,
    default: 'badge',
    validator: (value) => ['badge', 'icon', 'legend'].includes(value)
  }
})

const place = computed(() => niveauRank(props.niveau, props.niveaux))
</script>

<style scoped>
.niveau-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.2rem 0.75rem;
  border-radius: 999px;
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.4;
  color: #1f2937;
  border: 1px solid transparent;
  white-space: nowrap;
}

.niveau-badge--icon,
.niveau-badge--legend {
  padding: 0;
  border: none;
  background: none;
  font-weight: 500;
}

.niveau-badge--1 { background: #dcfce7; border-color: #86efac; }
.niveau-badge--2 { background: #e0f2fe; border-color: #7dd3fc; }
.niveau-badge--3 { background: #f3e8ff; border-color: #d8b4fe; }

/* Trois marches de hauteur croissante : la forme dit le niveau sans la couleur */
.niveau-steps {
  display: inline-flex;
  align-items: flex-end;
  gap: 2px;
  height: 0.9rem;
}

.niveau-step {
  width: 0.35rem;
  border: 1px solid #374151;
  border-radius: 1px;
  background: transparent;
}

.niveau-step:nth-child(1) { height: 40%; }
.niveau-step:nth-child(2) { height: 70%; }
.niveau-step:nth-child(3) { height: 100%; }
.niveau-step--on { background: #374151; }
</style>
