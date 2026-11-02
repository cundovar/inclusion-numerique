<template>
  <div>
    <PhoneSimulator
      :telephone="scene.telephone"
      :etat="etat"
      @evenement="recevoir"
    >
      <!-- Bulle de mission dans le téléphone : la consigne reste là où la personne agit -->
      <template #mission>
        <section
          ref="bulleMission"
          class="mx-2 mt-2 scroll-mt-32 rounded-2xl border-2 border-amber-400 bg-amber-50 p-3 text-gray-900 shadow-sm"
          aria-labelledby="mission-titre"
          data-testid="mission"
        >
          <div class="flex items-center justify-between gap-2">
            <h2
              id="mission-titre"
              ref="titreMission"
              tabindex="-1"
              data-focus-depart
              class="scroll-mt-32 text-base font-bold focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
            >
              <template v-if="!etat.termine">
                Mission {{ etat.etape + 1 }} sur {{ scene.missions.length }}
              </template>
              <template v-else>
                Toutes les missions sont faites
              </template>
            </h2>
            <!-- Progression : pastilles à l'écran, état écrit pour les lecteurs d'écran -->
            <ol
              class="flex gap-1"
              data-testid="progression"
            >
              <li
                v-for="(item, index) in scene.missions"
                :key="item.id"
              >
                <span
                  :class="[
                    index < etat.etape ? 'bg-green-700' : index === etat.etape ? 'bg-blue-800' : 'bg-white ring-1 ring-gray-500',
                    'block h-2.5 w-2.5 rounded-full',
                  ]"
                  aria-hidden="true"
                />
                <span class="sr-only">
                  {{ item.geste }} : {{ index < etat.etape ? 'faite' : index === etat.etape ? 'en cours' : 'à venir' }}
                </span>
              </li>
            </ol>
          </div>

          <!-- Dernière réussite, écrite et pas seulement en couleur -->
          <p
            v-if="derniereReussite"
            class="mt-1 flex items-start gap-2 text-sm font-bold text-green-800"
            data-testid="reussite"
          >
            <i
              class="fas fa-circle-check mt-0.5"
              aria-hidden="true"
            />
            {{ derniereReussite }}
          </p>

          <template v-if="mission">
            <p
              class="mt-1 text-lg font-bold leading-snug"
              data-testid="consigne-mission"
            >
              {{ mission.consigne }}
            </p>
            <button
              type="button"
              class="mt-1 inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg px-1 text-base font-bold text-blue-800 underline hover:bg-amber-100 focus-visible:outline-4 focus-visible:outline-blue-800"
              :aria-expanded="aideOuverte ? 'true' : 'false'"
              aria-controls="aide-mission"
              data-testid="bouton-aide"
              @click="aideOuverte = !aideOuverte"
            >
              <i
                class="fas fa-circle-question"
                aria-hidden="true"
              />
              {{ aideOuverte ? 'Masquer l\'aide' : 'Besoin d\'aide ?' }}
            </button>
            <p
              v-show="aideOuverte"
              id="aide-mission"
              class="mt-1 rounded-xl border border-amber-300 bg-white p-3 text-base text-gray-900"
              data-testid="aide"
            >
              {{ mission.aide }}
            </p>
          </template>

          <AppButton
            v-else
            variant="neutral"
            class="mt-3 min-h-11 w-full bg-blue-800 font-bold text-white! hover:bg-blue-900 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
            size="lg"
            text-content="Voir ce qu'il faut retenir"
            icon="fas fa-arrow-right"
            icon-position="right"
            data-testid="continuer"
            @click="$emit('reussie')"
          />
        </section>
      </template>
    </PhoneSimulator>

    <p class="mx-auto mt-3 max-w-sm text-base text-gray-700">
      Votre téléphone peut avoir un autre aspect. Les gestes restent les mêmes.
    </p>

    <!-- Annonces pour les lecteurs d'écran ; le même texte est visible dans la bulle -->
    <div
      role="status"
      aria-live="polite"
      aria-atomic="true"
      class="sr-only"
      data-testid="annonce"
    >
      {{ annonce }}
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import AppButton from '@/components/ui/AppButton.vue'
import PhoneSimulator from '../telephone/PhoneSimulator.vue'
import { appliquer, creerEtat } from '@/exercices/moteur/moteurTelephone'
import { amenerEnVue, garderVisible, montrerSansPerdre } from '../defilement'

const props = defineProps({
  scene: {
    type: Object,
    required: true,
  },
})

defineEmits(['reussie'])

// État du téléphone en mémoire seulement : rien n'est enregistré
const etat = ref(creerEtat(props.scene))
const aideOuverte = ref(false)
const derniereReussite = ref('')
const bulleMission = ref(null)
const titreMission = ref(null)

const mission = computed(() => props.scene.missions[etat.value.etape] ?? null)

const annonce = computed(() => {
  const morceaux = [etat.value.annonce, ...etat.value.reussites]
  if (etat.value.reussites.length && mission.value) {
    morceaux.push(`Mission suivante : ${mission.value.consigne}`)
  }
  return morceaux.filter(Boolean).join(' ')
})

// Élément que la personne utilise : le clavier s'il est ouvert, sinon ce qu'elle vient de toucher
const elementUtilise = (evenement) => {
  const clavier = document.querySelector('[data-testid="telephone"] [role="group"][aria-label="Clavier"]')
  if (clavier) return clavier
  const identifiant = evenement.type === 'taper' ? `touche-${evenement.touche}` : evenement.cible
  return [...document.querySelectorAll('[data-testid]')].find((element) => element.dataset.testid === identifiant)
}

const recevoir = async (evenement) => {
  const avant = etat.value.etape
  const clavierAvant = Boolean(document.querySelector('[data-testid="telephone"] [aria-label="Clavier"]'))
  etat.value = appliquer(props.scene, etat.value, evenement)
  await nextTick()
  const utilise = elementUtilise(evenement)
  // Un clavier qui vient d'apparaître ne doit pas rester caché sous les onglets du bas
  if (!clavierAvant && utilise?.getAttribute?.('aria-label') === 'Clavier') garderVisible(utilise)
  if (etat.value.etape === avant) return

  derniereReussite.value = etat.value.reussites.at(-1)
  // Nouvelle mission : l'aide de la précédente ne sert plus
  aideOuverte.value = false
  await nextTick()
  if (etat.value.termine) {
    // Fin : le bouton pour continuer est dans la bulle, on y amène le focus
    amenerEnVue(titreMission.value, { focus: true, bloc: 'start' })
  } else {
    // Sur un petit écran, la bulle peut être sortie de la vue : on la montre sans prendre le focus
    // et sans faire sortir de l'écran l'élément que la personne utilise (clavier, bouton…)
    montrerSansPerdre(bulleMission.value, utilise)
  }
}
</script>
