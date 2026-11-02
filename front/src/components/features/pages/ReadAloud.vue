<template>
  <section
    v-if="supported"
    class="read-aloud"
    aria-label="Lecture à voix haute"
  >
    <div class="read-aloud__controls">
      <button
        type="button"
        class="read-aloud__button"
        :class="{ 'read-aloud__button--on': status !== 'idle' }"
        @click="toggle"
      >
        <i
          :class="isPlaying ? 'fas fa-pause' : 'fas fa-volume-high'"
          aria-hidden="true"
        />
        {{ toggleLabel }}
      </button>

      <button
        v-if="status !== 'idle'"
        type="button"
        class="read-aloud__button read-aloud__button--stop"
        @click="stop"
      >
        <i
          class="fas fa-stop"
          aria-hidden="true"
        />
        Arrêter
      </button>

      <label class="read-aloud__rate">
        <span>Vitesse</span>
        <select
          :value="rate"
          @change="setRate(Number($event.target.value))"
        >
          <option
            v-for="option in RATES"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
      </label>
    </div>

    <!-- Annonce vocale pour les lecteurs d'écran, sans répéter le texte du cours -->
    <p
      class="read-aloud__status"
      role="status"
      aria-live="polite"
    >
      {{ statusText }}
    </p>
  </section>
</template>

<script setup>
import { computed, toRef } from 'vue'
import { useReadAloud, RATES } from '@/composables/ui/useReadAloud'

const props = defineProps({
  // Élément du DOM qui contient le texte du cours à lire
  target: {
    type: Object,
    default: null
  }
})

const { supported, status, isPlaying, rate, progress, toggle, stop, setRate } = useReadAloud(toRef(props, 'target'))

const toggleLabel = computed(() => {
  if (status.value === 'playing') return 'Mettre en pause'
  if (status.value === 'paused') return 'Reprendre la lecture'
  return 'Écouter ce cours'
})

const statusText = computed(() => {
  if (status.value === 'playing') return `Lecture en cours, passage ${progress.value.current + 1} sur ${progress.value.total}`
  if (status.value === 'paused') return 'Lecture en pause'
  return ''
})
</script>

<style scoped>
/* Mêmes teintes pastel que « Télécharger en PDF » : fonds clairs, texte foncé (voir AGENTS.md) */
.read-aloud__controls {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.read-aloud__button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  min-height: 2.75rem;
  padding: 0.5rem 1rem;
  border-radius: 12px;
  border: 1px solid #93c5fd;
  background: #eff6ff;
  color: #1e3a8a;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
}

.read-aloud__button:hover { background: #dbeafe; }

/* Lecture lancée : vert clair, pour voir d'un coup d'œil que le site parle */
.read-aloud__button--on {
  border-color: #86efac;
  background: #dcfce7;
  color: #14532d;
}

.read-aloud__button--on:hover { background: #bbf7d0; }

.read-aloud__button--stop {
  border-color: #d1d5db;
  background: #fff;
  color: #374151;
}

.read-aloud__button--stop:hover { background: #f3f4f6; }

.read-aloud__button:focus-visible,
.read-aloud__rate select:focus-visible {
  outline: 3px solid #1d4ed8;
  outline-offset: 2px;
}

.read-aloud__rate {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #1f2937;
}

.read-aloud__rate select {
  min-height: 2.75rem;
  padding: 0.25rem 0.75rem;
  border: 1px solid #93c5fd;
  border-radius: 12px;
  background: #fff;
  color: #1e3a8a;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

.read-aloud__status {
  min-height: 1.25rem;
  margin: 0.5rem 0 0;
  font-size: 0.9rem;
  color: #374151;
}

.read-aloud__status:empty {
  display: none;
}

/* La lecture n'a pas de sens sur papier */
@media print {
  .read-aloud {
    display: none;
  }
}
</style>
