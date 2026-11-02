<template>
  <!-- Cadre seulement à partir de la tablette : sur un téléphone, le faux écran prend la largeur -->
  <div
    ref="racine"
    class="mx-auto w-full max-w-sm md:rounded-[2.5rem] md:border-[12px] md:border-gray-900 md:shadow-2xl"
  >
    <div
      role="group"
      :aria-label="`Écran du ${telephone.nom}`"
      class="flex min-h-[30rem] flex-col overflow-hidden rounded-xl border-2 border-gray-300 bg-slate-100 md:rounded-[1.75rem] md:border-0"
      data-testid="telephone"
      @keydown="clavierPhysique"
    >
      <!-- Barre d'état -->
      <div
        class="flex items-center justify-between bg-slate-200 px-4 py-1 text-sm font-bold text-gray-900"
        data-testid="barre-etat"
      >
        <span>{{ telephone.heure }}</span>
        <span class="flex items-center gap-3">
          <span v-if="etat.valeurs.wifi">
            <i
              class="fas fa-wifi"
              aria-hidden="true"
            />
            <span class="sr-only">Wi-Fi activé</span>
          </span>
          <span
            v-else
            class="sr-only"
          >Wi-Fi désactivé</span>
          <span>
            <i
              :class="iconeVolume"
              aria-hidden="true"
            />
            <span class="sr-only">Volume</span>
            {{ etat.valeurs.volume }}
          </span>
          <i
            class="fas fa-battery-three-quarters"
            aria-hidden="true"
          />
        </span>
      </div>

      <!-- Emplacement fourni par la scène (bulle de mission de l'exercice) -->
      <slot name="mission" />

      <!-- En-tête de l'application ouverte -->
      <div
        :class="[ecran.parent ? 'justify-between' : 'justify-center', 'flex min-h-14 items-center gap-2 bg-white px-2']"
      >
        <button
          v-if="ecran.parent"
          type="button"
          class="flex min-h-11 min-w-11 cursor-pointer items-center justify-center gap-1 rounded-lg px-2 text-base font-bold text-blue-800 hover:bg-blue-50 focus-visible:outline-4 focus-visible:outline-blue-800"
          data-testid="bouton-retour"
          @click="emettre({ type: 'toucher', cible: 'retour' })"
        >
          <i
            class="fas fa-chevron-left"
            aria-hidden="true"
          />
          Retour
        </button>
        <h3
          ref="titreEcran"
          tabindex="-1"
          class="text-xl font-bold text-gray-900 focus-visible:outline-4 focus-visible:outline-blue-800"
          data-testid="titre-ecran"
        >
          {{ ecran.titre }}
        </h3>
        <span
          v-if="ecran.parent"
          class="min-w-11"
          aria-hidden="true"
        />
      </div>

      <!-- Contenu de l'écran -->
      <div class="flex-1 p-3">
        <ul
          v-if="applications.length"
          class="grid grid-cols-3 gap-3"
        >
          <li
            v-for="zone in applications"
            :key="zone.id"
          >
            <button
              type="button"
              :data-testid="zone.id"
              class="flex min-h-11 w-full cursor-pointer flex-col items-center gap-1 rounded-xl p-2 text-sm font-bold text-gray-900 hover:bg-white focus-visible:outline-4 focus-visible:outline-blue-800"
              @click="emettre({ type: 'toucher', cible: zone.id })"
            >
              <span
                :class="[zone.couleur, 'flex h-14 w-14 items-center justify-center rounded-2xl text-2xl text-white']"
                aria-hidden="true"
              >
                <i :class="zone.icone" />
              </span>
              {{ zone.libelle }}
            </button>
          </li>
        </ul>

        <template
          v-for="zone in autresZones"
          :key="zone.id"
        >
          <!-- Liste à faire défiler : geste, molette ou boutons -->
          <div
            v-if="zone.type === 'liste'"
            :data-testid="zone.id"
          >
            <div
              class="liste-glissable overflow-hidden rounded-xl bg-white"
              :style="{ height: `${zone.visibles * HAUTEUR_ELEMENT}rem` }"
              data-testid="zone-glisser"
              @pointerdown="debutGlisser"
              @pointerup="finGlisser(zone, $event)"
              @pointercancel="annulerGlisser"
              @wheel="molette(zone, $event)"
            >
              <ul
                class="motion-safe:transition-transform motion-safe:duration-300"
                :style="{ transform: `translateY(-${cran(zone) * HAUTEUR_ELEMENT}rem)` }"
              >
                <li
                  v-for="(element, index) in zone.elements"
                  :key="index"
                  :aria-hidden="estVisible(zone, index) ? undefined : 'true'"
                  class="flex flex-col justify-center border-b border-gray-200 px-3"
                  :style="{ height: `${HAUTEUR_ELEMENT}rem` }"
                >
                  <span class="font-bold text-gray-900">{{ element.de }}</span>
                  <span class="text-gray-700">{{ element.texte }}</span>
                </li>
              </ul>
            </div>
            <div class="mt-2 grid grid-cols-2 gap-2">
              <button
                type="button"
                class="min-h-11 cursor-pointer rounded-lg border-2 border-blue-800 bg-white px-2 text-sm font-bold text-blue-800 hover:bg-blue-50 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
                data-testid="defiler-haut"
                @click="emettre({ type: 'glisser', cible: zone.id, direction: 'haut' })"
              >
                <i
                  class="fas fa-arrow-up"
                  aria-hidden="true"
                />
                Faire défiler vers le haut
              </button>
              <button
                type="button"
                class="min-h-11 cursor-pointer rounded-lg border-2 border-blue-800 bg-white px-2 text-sm font-bold text-blue-800 hover:bg-blue-50 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
                data-testid="defiler-bas"
                @click="emettre({ type: 'glisser', cible: zone.id, direction: 'bas' })"
              >
                <i
                  class="fas fa-arrow-down"
                  aria-hidden="true"
                />
                Faire défiler vers le bas
              </button>
            </div>
          </div>

          <!-- Curseur : champ standard du navigateur -->
          <div
            v-else-if="zone.type === 'curseur'"
            class="mb-3 rounded-xl bg-white p-4"
          >
            <label
              :for="`zone-${zone.id}`"
              class="flex items-center justify-between text-lg font-bold text-gray-900"
            >
              {{ zone.libelle }}
              <output
                :for="`zone-${zone.id}`"
                class="text-base font-normal"
              >{{ etat.valeurs[zone.valeur] }} sur {{ zone.max }}</output>
            </label>
            <input
              :id="`zone-${zone.id}`"
              type="range"
              :min="zone.min"
              :max="zone.max"
              :step="zone.pas"
              :value="etat.valeurs[zone.valeur]"
              :data-testid="zone.id"
              class="mt-3 h-11 w-full cursor-pointer accent-blue-800 focus-visible:outline-4 focus-visible:outline-blue-800"
              @input="emettre({ type: 'regler', cible: zone.id, valeur: $event.target.value })"
            >
          </div>

          <!-- Interrupteur : bouton avec état écrit, pas seulement en couleur -->
          <div
            v-else-if="zone.type === 'interrupteur'"
            class="mb-3 rounded-xl bg-white p-4"
          >
            <button
              type="button"
              role="switch"
              :aria-checked="etat.valeurs[zone.valeur] ? 'true' : 'false'"
              :data-testid="zone.id"
              class="flex min-h-11 w-full cursor-pointer items-center justify-between gap-3 text-lg font-bold text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-blue-800"
              @click="emettre({ type: 'toucher', cible: zone.id })"
            >
              {{ zone.libelle }}
              <span class="flex items-center gap-2 text-base font-normal">
                {{ etat.valeurs[zone.valeur] ? 'Activé' : 'Désactivé' }}
                <span
                  :class="[
                    etat.valeurs[zone.valeur] ? 'justify-end bg-green-700' : 'justify-start bg-gray-400',
                    'flex h-8 w-14 items-center rounded-full p-1 motion-safe:transition-colors',
                  ]"
                  aria-hidden="true"
                >
                  <span class="h-6 w-6 rounded-full bg-white shadow" />
                </span>
              </span>
            </button>
          </div>

          <!-- Conversation : messages reçus à gauche, envoyés à droite, avec l'auteur écrit -->
          <ol
            v-else-if="zone.type === 'conversation'"
            :aria-label="`Conversation avec ${zone.avec}`"
            class="mb-3 flex flex-col gap-2"
            :data-testid="zone.id"
          >
            <li
              v-for="(texte, index) in zone.recus"
              :key="`recu-${index}`"
              class="max-w-[80%] self-start rounded-2xl rounded-bl-sm bg-white px-3 py-2 text-gray-900 shadow-sm"
            >
              <span class="block text-sm font-bold">{{ zone.avec }}</span>
              {{ texte }}
            </li>
            <li
              v-for="(texte, index) in etat.envoyes"
              :key="`envoye-${index}`"
              class="max-w-[80%] self-end rounded-2xl rounded-br-sm bg-blue-800 px-3 py-2 text-white"
              data-testid="message-envoye"
            >
              <span class="block text-sm font-bold">Vous</span>
              {{ texte }}
            </li>
          </ol>

          <!-- Zone de texte fictive (un vrai champ ouvrirait le clavier de l'appareil) et bouton Envoyer -->
          <div
            v-else-if="zone.type === 'champ'"
            class="mb-3"
          >
            <!-- Conseil (envoi refusé, message vide) : affiché là où la personne regarde -->
            <p
              v-if="etat.conseil"
              class="mb-2 flex items-start gap-2 rounded-xl border-l-8 border-amber-600 bg-amber-50 p-3 text-base text-gray-900"
              data-testid="conseil"
            >
              <i
                class="fas fa-circle-info mt-1 text-amber-800"
                aria-hidden="true"
              />
              {{ etat.conseil }}
            </p>
            <div class="flex items-stretch gap-2">
              <button
                type="button"
                :data-testid="zone.id"
                :class="[
                  etat.valeurs[zone.ouvre] ? 'border-blue-800' : 'border-gray-400',
                  'min-h-11 flex-1 cursor-text rounded-2xl border-2 bg-white px-3 py-2 text-left text-lg text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800',
                ]"
                @click="emettre({ type: 'toucher', cible: zone.id })"
              >
                <span class="sr-only">Zone de texte « {{ zone.libelle }} » : </span>
                <span
                  v-if="etat.valeurs[zone.valeur]"
                  class="whitespace-pre-wrap break-words"
                >{{ etat.valeurs[zone.valeur] }}</span>
                <span
                  v-else
                  class="text-gray-600"
                >{{ zone.libelle }}</span>
                <span
                  v-if="etat.valeurs[zone.ouvre]"
                  class="curseur-texte"
                  aria-hidden="true"
                >|</span>
              </button>
              <button
                v-for="envoi in zonesEnvoi"
                :key="envoi.id"
                type="button"
                :data-testid="envoi.id"
                class="flex min-h-11 min-w-11 cursor-pointer flex-col items-center justify-center rounded-2xl bg-blue-800 px-3 text-sm font-bold text-white hover:bg-blue-900 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
                @click="emettre({ type: 'toucher', cible: envoi.id })"
              >
                <i
                  class="fas fa-paper-plane"
                  aria-hidden="true"
                />
                {{ envoi.libelle }}
              </button>
            </div>
          </div>

          <!-- Clavier tactile fictif : touches réalistes (environ 30 × 48 px), exception documentée à la règle des 44 px -->
          <div
            v-else-if="zone.type === 'clavier' && etat.valeurs[zone.ouvert]"
            role="group"
            aria-label="Clavier"
            class="-mx-3 -mb-3 mt-1 bg-slate-300 p-1"
            :data-testid="zone.id"
          >
            <div
              v-for="(rangee, index) in zone.rangees"
              :key="index"
              class="mb-1 flex gap-1"
            >
              <button
                v-for="touche in rangee"
                :key="touche"
                type="button"
                :data-testid="`touche-${touche}`"
                :aria-label="NOMS_TOUCHES[touche]"
                :class="[
                  touche === 'effacer' ? 'flex-[1.8] flex-col gap-0.5 bg-slate-800 text-white' : 'flex-1 gap-1 bg-white text-gray-900',
                  'flex h-12 min-w-0 cursor-pointer items-center justify-center rounded-md text-lg font-bold shadow-sm focus-visible:outline-4 focus-visible:outline-blue-800 active:bg-blue-100',
                ]"
                @click="emettre({ type: 'taper', cible: zone.id, touche })"
              >
                <template v-if="touche === 'effacer'">
                  <i
                    class="fas fa-delete-left"
                    aria-hidden="true"
                  />
                  <span class="text-[0.7rem] leading-none">Effacer</span>
                </template>
                <template v-else-if="touche === 'espace'">
                  <span class="text-base font-normal">espace</span>
                </template>
                <template v-else>
                  {{ majusculeAuto(etat.valeurs[zone.valeur]) ? touche.toUpperCase() : touche }}
                </template>
              </button>
            </div>
          </div>

          <p
            v-else-if="zone.type === 'texte'"
            class="rounded-xl bg-white p-4 text-lg text-gray-800"
          >
            {{ zone.texte }}
          </p>
        </template>
      </div>

      <!-- Bouton Accueil, toujours présent -->
      <div class="flex justify-center bg-slate-200 p-2">
        <button
          type="button"
          class="flex min-h-11 cursor-pointer items-center gap-2 rounded-full bg-white px-6 text-base font-bold text-gray-900 shadow hover:bg-gray-50 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
          data-testid="bouton-accueil"
          @click="emettre({ type: 'toucher', cible: 'accueil' })"
        >
          <i
            class="fas fa-house"
            aria-hidden="true"
          />
          Accueil
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { defilementMax, majusculeAuto } from '@/exercices/moteur/moteurTelephone'

const props = defineProps({
  telephone: {
    type: Object,
    required: true,
  },
  etat: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['evenement'])

// Hauteur d'un élément de liste, en rem
const HAUTEUR_ELEMENT = 4
// Distance minimale, en pixels, pour qu'un mouvement compte comme un glissement
const SEUIL_GLISSER = 30

const ecran = computed(() => props.telephone.ecrans[props.etat.ecran])
const applications = computed(() => ecran.value.zones.filter((zone) => zone.type === 'application'))
// Le bouton Envoyer s'affiche à côté de la zone de texte, pas à part
const autresZones = computed(() => ecran.value.zones.filter((zone) => !['application', 'envoyer'].includes(zone.type)))
const zonesEnvoi = computed(() => ecran.value.zones.filter((zone) => zone.type === 'envoyer'))

// Nom lu par les lecteurs d'écran pour les touches sans lettre
const NOMS_TOUCHES = { effacer: 'Effacer', espace: 'Espace', ',': 'Virgule', '\'': 'Apostrophe' }

const iconeVolume = computed(() => {
  if (props.etat.valeurs.volume === 0) return 'fas fa-volume-xmark'
  return props.etat.valeurs.volume >= 7 ? 'fas fa-volume-high' : 'fas fa-volume-low'
})

const emettre = (evenement) => emit('evenement', evenement)

const cran = (zone) => props.etat.defilement[zone.id] ?? 0
const estVisible = (zone, index) => index >= cran(zone) && index < cran(zone) + zone.visibles

// Glissement au doigt ou à la souris : un cran par hauteur d'élément parcourue
let depart = null
const debutGlisser = (event) => {
  depart = event.clientY
  event.currentTarget.setPointerCapture?.(event.pointerId)
}
const annulerGlisser = () => {
  depart = null
}
const finGlisser = (zone, event) => {
  if (depart === null) return
  const ecart = event.clientY - depart
  depart = null
  if (Math.abs(ecart) < SEUIL_GLISSER) return
  const taillePolice = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16
  const pixelsParElement = HAUTEUR_ELEMENT * taillePolice
  const crans = Math.max(1, Math.round(Math.abs(ecart) / pixelsParElement))
  // Le doigt monte : le contenu défile vers le bas, comme sur un vrai téléphone
  const direction = ecart < 0 ? 'bas' : 'haut'
  for (let i = 0; i < crans; i += 1) emettre({ type: 'glisser', cible: zone.id, direction })
}

// Molette : la page ne défile pas tant que la liste peut encore bouger.
// Un pavé tactile envoie des dizaines d'événements : un cran au plus toutes les 250 ms.
const DELAI_MOLETTE = 250
let derniereMolette = 0
const molette = (zone, event) => {
  if (Math.abs(event.deltaY) < 1) return
  const direction = event.deltaY > 0 ? 'bas' : 'haut'
  const peutBouger = direction === 'bas' ? cran(zone) < defilementMax(zone) : cran(zone) > 0
  if (!peutBouger) return
  event.preventDefault()
  if (event.timeStamp - derniereMolette < DELAI_MOLETTE) return
  derniereMolette = event.timeStamp
  emettre({ type: 'glisser', cible: zone.id, direction })
}

// Sur ordinateur, les lettres et la touche d'effacement du vrai clavier écrivent aussi dans le faux
const clavierPhysique = (event) => {
  if (event.ctrlKey || event.metaKey || event.altKey) return
  const clavier = ecran.value.zones.find((zone) => zone.type === 'clavier' && props.etat.valeurs[zone.ouvert])
  if (!clavier || event.target.type === 'range') return
  let touche = null
  if (event.key === 'Backspace') touche = 'effacer'
  else if (/^[a-z,']$/i.test(event.key)) touche = event.key.toLowerCase()
  if (!touche) return
  event.preventDefault()
  emettre({ type: 'taper', cible: clavier.id, touche })
}

// Le bouton touché disparaît avec l'écran : le focus passe au titre du nouvel écran,
// sauf si la page l'a déjà placé ailleurs (par exemple sur la fin de l'exercice)
const racine = ref(null)
const titreEcran = ref(null)
watch(() => props.etat.ecran, async () => {
  await nextTick()
  const actif = document.activeElement
  if (actif && actif !== document.body && !racine.value?.contains(actif)) return
  titreEcran.value?.focus({ preventScroll: true })
})
</script>

<style scoped>
/* Curseur de saisie qui clignote, seulement si les animations sont acceptées */
@media (prefers-reduced-motion: no-preference) {
  .curseur-texte {
    animation: clignoter 1s step-end infinite;
  }
}

@keyframes clignoter {
  50% {
    opacity: 0;
  }
}

/* Le geste sur la liste est géré par la page ; ailleurs, le défilement normal reste possible */
.liste-glissable {
  touch-action: none;
  user-select: none;
}
</style>
