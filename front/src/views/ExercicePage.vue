<template>
  <div class="min-h-screen px-4 pb-40 pt-6 md:px-10 xl:pt-0">
    <div class="mx-auto max-w-3xl">
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
          <li>
            <router-link
              to="/exercices"
              class="text-blue-800 underline"
            >
              Exercices
            </router-link>
          </li>
          <li aria-hidden="true">
            <i class="fas fa-chevron-right text-sm" />
          </li>
          <li aria-current="page">
            {{ exercice ? exercice.titre : 'Exercice introuvable' }}
          </li>
        </ol>
      </nav>

      <template v-if="exercice">
        <header class="mt-6">
          <h1 class="text-3xl font-bold text-gray-900 md:text-4xl">
            {{ exercice.titre }}
          </h1>
          <p class="mt-2 inline-flex items-center gap-2 text-lg text-gray-700">
            <i
              class="fas fa-clock"
              aria-hidden="true"
            />
            Durée : {{ exercice.duree }}
          </p>
        </header>

        <!-- Consigne compacte : sur mobile, l'exercice doit apparaître vite sous le titre -->
        <section
          class="mt-4 rounded-2xl bg-blue-50 px-4 py-3"
          aria-labelledby="consigne-titre"
        >
          <h2
            id="consigne-titre"
            class="text-base font-bold text-gray-900"
          >
            Consigne
          </h2>
          <p class="mt-1 text-lg text-gray-900">
            {{ exercice.consigne }}
          </p>
        </section>

        <div class="mt-5">
          <ScenePlayer
            :key="exercice.slug"
            :exercice="exercice"
          />
        </div>
      </template>

      <div
        v-else
        class="mt-6"
        data-testid="exercice-introuvable"
      >
        <h1 class="text-3xl font-bold text-gray-900 md:text-4xl">
          Exercice introuvable
        </h1>
        <p class="mt-4 text-lg text-gray-800">
          Cet exercice n'existe pas ou a changé d'adresse.
        </p>
        <router-link
          to="/exercices"
          class="mt-4 inline-flex min-h-11 items-center text-lg font-bold text-blue-800 underline"
        >
          Voir tous les exercices
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { trouverExercice } from '@/exercices'
import ScenePlayer from '@/components/features/exercices/ScenePlayer.vue'

const props = defineProps({
  slug: {
    type: String,
    required: true,
  },
})

const exercice = computed(() => trouverExercice(props.slug))
</script>
