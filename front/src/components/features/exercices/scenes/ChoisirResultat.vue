<template>
  <div>
    <!-- Fausse page de résultats : tout est fictif -->
    <section
      class="rounded-2xl border-2 border-gray-300 bg-white p-4 shadow md:p-6"
      aria-labelledby="resultats-titre"
    >
      <!-- Point de départ du focus quand l'exercice recommence -->
      <h2
        id="resultats-titre"
        tabindex="-1"
        data-focus-depart
        class="scroll-mt-32 text-xl font-bold text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-blue-800"
      >
        Résultats de la recherche
      </h2>
      <p class="mt-1 text-base text-gray-700">
        Exemple fictif, pour s'entraîner.
      </p>

      <ul
        class="mt-4 flex flex-col gap-4"
        data-testid="resultats"
      >
        <li
          v-for="resultat in scene.resultats"
          :key="resultat.id"
          :data-testid="`item-${resultat.id}`"
        >
          <button
            type="button"
            :data-testid="`resultat-${resultat.id}`"
            :class="[
              'block min-h-11 w-full cursor-pointer rounded-xl border-2 p-4 text-left motion-safe:transition-colors',
              'focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800',
              classesResultat(resultat),
            ]"
            @click="choisir(resultat)"
          >
            <span
              v-if="choixId === resultat.id"
              class="mb-2 inline-flex items-center gap-2 rounded-full bg-gray-900 px-3 py-1 text-sm font-bold text-white"
            >
              <i
                class="fas fa-hand-pointer"
                aria-hidden="true"
              />
              Votre choix
            </span>
            <span class="flex flex-wrap items-center gap-2">
              <span
                v-if="resultat.etiquette"
                class="rounded border-2 border-gray-900 px-2 text-sm font-bold text-gray-900"
              >
                {{ resultat.etiquette }}
              </span>
              <span class="text-xl font-bold text-blue-800 underline">
                {{ resultat.titre }}
              </span>
            </span>
            <span class="mt-1 block break-all text-base text-green-900">
              {{ resultat.adresse }}
            </span>
            <span class="mt-1 block text-lg text-gray-800">
              {{ resultat.extrait }}
            </span>
          </button>

          <!-- Retour visible juste sous le résultat choisi, pour le voir sans chercher -->
          <div
            v-if="choixId === resultat.id"
            ref="retourVisible"
            data-testid="retour-visible"
            :class="[
              'mt-2 scroll-mt-32 scroll-mb-28 rounded-2xl border-l-8 p-5 text-lg text-gray-900',
              resultat.correct ? 'border-green-700 bg-green-50' : 'border-amber-600 bg-amber-50',
            ]"
          >
            <p class="flex items-center gap-2 font-bold">
              <i
                :class="resultat.correct ? 'fas fa-circle-check text-green-800' : 'fas fa-circle-info text-amber-800'"
                aria-hidden="true"
              />
              {{ entete(resultat) }}
            </p>
            <p class="mt-2">
              {{ resultat.retour }}
            </p>
            <p
              v-if="!resultat.correct"
              class="mt-2"
            >
              {{ RELANCE }}
            </p>
            <AppButton
              v-else
              class="mt-4 min-h-11 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
              size="lg"
              text-content="Voir ce qu'il faut retenir"
              icon="fas fa-arrow-right"
              icon-position="right"
              data-testid="continuer"
              @click="$emit('reussie')"
            />
          </div>
        </li>
      </ul>
    </section>

    <!--
      Région annoncée par les lecteurs d'écran : toujours présente dans le DOM,
      masquée à l'écran car le même texte est déjà visible sous le résultat choisi.
    -->
    <div
      role="status"
      aria-live="polite"
      aria-atomic="true"
      data-testid="retour"
      class="sr-only"
    >
      <template v-if="choix">
        {{ entete(choix) }} {{ choix.retour }} {{ choix.correct ? '' : RELANCE }}
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import AppButton from '@/components/ui/AppButton.vue'
import { amenerEnVue } from '../defilement'

const props = defineProps({
  scene: {
    type: Object,
    required: true,
  },
})

defineEmits(['reussie'])

const RELANCE = 'Vous pouvez choisir un autre résultat.'

// Choix en cours, gardé en mémoire seulement le temps de la page
const choixId = ref(null)
const choix = computed(() => props.scene.resultats.find((resultat) => resultat.id === choixId.value) ?? null)
const retourVisible = ref([])

const entete = (resultat) => (resultat.correct ? 'Bon choix.' : 'Ce n\'est pas le bon site.')

const choisir = async (resultat) => {
  choixId.value = resultat.id
  // Le focus reste sur le bouton ; on fait seulement défiler pour que le retour soit visible
  await nextTick()
  amenerEnVue(retourVisible.value[0])
}

// La couleur double toujours une information donnée en texte (« Votre choix », « Bon choix. »)
const classesResultat = (resultat) => {
  if (choixId.value !== resultat.id) {
    return 'border-gray-200 bg-white hover:border-blue-700 hover:bg-blue-50'
  }
  return resultat.correct ? 'border-green-700 bg-green-50' : 'border-amber-600 bg-amber-50'
}
</script>
