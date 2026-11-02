<template>
  <div class="min-h-screen px-4 pb-40 pt-6 md:px-10 xl:pt-0">
    <div class="mx-auto max-w-5xl">
      <!-- Fil d'Ariane -->
      <nav
        aria-label="Fil d'Ariane"
        class="text-lg"
      >
        <ol class="flex flex-wrap items-center gap-2 text-gray-700">
          <li>
            <router-link
              to="/"
              class="text-blue-800 underline"
            >
              Accueil
            </router-link>
          </li>
          <li aria-hidden="true">
            <i class="fas fa-chevron-right text-sm" />
          </li>
          <li aria-current="page">
            Exercices
          </li>
        </ol>
      </nav>

      <header class="mt-6">
        <div class="flex items-center gap-4">
          <span
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-gray-900 md:h-16 md:w-16"
            aria-hidden="true"
          >
            <i class="fas fa-puzzle-piece text-2xl md:text-3xl" />
          </span>
          <h1 class="text-3xl font-bold text-gray-900 md:text-4xl">
            Exercices
          </h1>
        </div>
        <p class="mt-2 text-xl text-gray-700">
          Entraînez-vous sur des situations imaginaires. Vous pouvez vous tromper : rien n'est enregistré.
        </p>
      </header>

      <section
        v-for="groupe in groupes"
        :key="groupe.audience.key"
        class="mt-10"
        :aria-labelledby="`exercices-${groupe.audience.key}`"
      >
        <h2
          :id="`exercices-${groupe.audience.key}`"
          class="text-2xl font-bold text-gray-900"
        >
          {{ groupe.audience.title }}
        </h2>

        <ul
          class="mt-6 grid gap-6 md:grid-cols-2"
          :data-testid="`exercices-${groupe.audience.key}`"
        >
          <li
            v-for="exercice in groupe.exercices"
            :key="exercice.slug"
            :class="['surface-card', groupe.audience.key === 'apprendre' ? 'surface-card--blue' : 'surface-card--green', 'exercice-card relative flex flex-col p-6']"
          >
            <h3 class="text-2xl font-bold text-gray-900">
              <router-link
                :to="`/exercices/${exercice.slug}`"
                class="exercice-link"
              >
                {{ exercice.titre }}
              </router-link>
            </h3>
            <p class="mt-4 flex flex-wrap items-center gap-3 text-base text-gray-700">
              <span class="inline-flex items-center gap-2">
                <i
                  class="fas fa-clock"
                  aria-hidden="true"
                />
                Durée : {{ exercice.duree }}
              </span>
            </p>
            <p class="mt-2 text-base text-gray-700">
              Compétence : {{ exercice.competence }}
            </p>
          </li>
        </ul>
      </section>

      <p
        v-if="!groupes.length"
        class="mt-10 rounded-2xl bg-white p-6 text-lg text-gray-700 shadow"
        data-testid="exercices-vide"
      >
        Les premiers exercices sont en préparation. Revenez bientôt !
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { AUDIENCES } from '@/config/audiences'
import { listerExercices } from '@/exercices'

// Un groupe par public, seulement s'il a des exercices
const groupes = computed(() =>
  AUDIENCES
    .map((audience) => ({
      audience,
      exercices: listerExercices().filter((exercice) => exercice.public === audience.key),
    }))
    .filter((groupe) => groupe.exercices.length > 0)
)
</script>

<style scoped>
/* Le lien du titre couvre toute la carte */
.exercice-link::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 1rem;
}

.exercice-link:focus {
  outline: none;
}

</style>
