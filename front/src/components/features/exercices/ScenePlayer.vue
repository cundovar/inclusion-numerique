<template>
  <div>
    <!-- Scène en cours -->
    <div
      v-if="!termine"
      ref="zoneScene"
    >
      <component
        :is="composantScene"
        v-if="composantScene"
        :key="indexScene"
        :scene="sceneCourante"
        @reussie="sceneReussie"
      />
      <p
        v-else
        class="rounded-2xl bg-white p-6 text-lg text-gray-700 shadow"
        data-testid="scene-inconnue"
      >
        Cette étape ne peut pas s'afficher pour le moment.
      </p>
    </div>

    <!-- Conclusion -->
    <section
      v-else
      class="surface-card surface-card--green rounded-2xl p-6"
      aria-labelledby="conclusion-titre"
      data-testid="conclusion"
    >
      <h2
        id="conclusion-titre"
        ref="titreConclusion"
        tabindex="-1"
        class="flex scroll-mt-32 items-center gap-3 text-2xl font-bold text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-blue-800"
      >
        <i
          class="fas fa-circle-check text-green-800"
          aria-hidden="true"
        />
        {{ exercice.conclusion.reussite }}
      </h2>

      <h3 class="mt-6 text-xl font-bold text-gray-900">
        À retenir
      </h3>
      <ul class="mt-3 list-disc space-y-2 pl-6 text-lg text-gray-800">
        <li
          v-for="phrase in exercice.conclusion.aRetenir"
          :key="phrase"
        >
          {{ phrase }}
        </li>
      </ul>

      <div class="mt-8 flex flex-wrap items-center gap-4">
        <AppButton
          class="min-h-11 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
          size="lg"
          variant="outline"
          icon="fas fa-rotate-left"
          text-content="Recommencer l'exercice"
          data-testid="recommencer"
          @click="recommencer"
        />
        <router-link
          to="/exercices"
          class="inline-flex min-h-11 items-center text-lg font-bold text-blue-800 underline"
        >
          Voir tous les exercices
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import AppButton from '@/components/ui/AppButton.vue'
import ChoisirResultat from './scenes/ChoisirResultat.vue'
import SceneFormulaire from './scenes/SceneFormulaire.vue'
import SceneTelephone from './scenes/SceneTelephone.vue'
import { amenerEnVue } from './defilement'

// Registre des types de scènes : un nouveau type s'ajoute ici
const TYPES_DE_SCENES = {
  'choisir-resultat': ChoisirResultat,
  telephone: SceneTelephone,
  formulaire: SceneFormulaire,
}

const props = defineProps({
  exercice: {
    type: Object,
    required: true,
  },
})

// Progression gardée en mémoire seulement : rien n'est enregistré
const indexScene = ref(0)
const termine = ref(false)
const titreConclusion = ref(null)
const zoneScene = ref(null)

const sceneCourante = computed(() => props.exercice.scenes[indexScene.value])
const composantScene = computed(() => TYPES_DE_SCENES[sceneCourante.value?.type] ?? null)

const sceneReussie = async () => {
  if (indexScene.value < props.exercice.scenes.length - 1) {
    indexScene.value += 1
    await nextTick()
    amenerEnVue(zoneScene.value?.querySelector('[data-focus-depart]'), { focus: true, bloc: 'start' })
    return
  }
  termine.value = true
  // Le focus va sur la conclusion pour que le lecteur d'écran la lise ;
  // scroll-mt-32 garde le titre sous l'en-tête fixe
  await nextTick()
  amenerEnVue(titreConclusion.value, { focus: true, bloc: 'start' })
}

const recommencer = async () => {
  indexScene.value = 0
  termine.value = false
  // Le bouton cliqué disparaît : on ramène le focus au début de la scène plutôt qu'en haut de la page
  await nextTick()
  amenerEnVue(zoneScene.value?.querySelector('[data-focus-depart]'), { focus: true, bloc: 'start' })
}
</script>
