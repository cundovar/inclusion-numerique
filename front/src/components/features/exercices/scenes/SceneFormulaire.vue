<template>
  <div class="grid gap-6 lg:grid-cols-[18rem_1fr] lg:items-start">
    <!-- Fiche de la personne imaginaire : les informations à recopier -->
    <aside
      class="rounded-2xl border-2 border-amber-300 bg-amber-50 p-5 text-gray-900 lg:sticky lg:top-32"
      aria-labelledby="fiche-titre"
      data-testid="fiche"
    >
      <h2
        id="fiche-titre"
        class="flex items-center gap-2 text-lg font-bold"
      >
        <i
          class="fas fa-id-card"
          aria-hidden="true"
        />
        {{ scene.fiche.titre }}
      </h2>
      <dl class="mt-3 space-y-2 text-lg">
        <div
          v-for="ligne in scene.fiche.lignes"
          :key="ligne.libelle"
        >
          <dt class="text-base text-gray-700">
            {{ ligne.libelle }}
          </dt>
          <dd class="font-bold">
            {{ ligne.valeur }}
          </dd>
        </div>
      </dl>
    </aside>

    <!-- Fausse fenêtre de navigateur : tout est fictif, rien n'est envoyé -->
    <section
      class="overflow-hidden rounded-2xl border-2 border-gray-300 bg-white shadow"
      aria-label="Site imaginaire du Service Exemple"
      data-testid="navigateur"
    >
      <div class="flex items-center gap-3 border-b border-gray-300 bg-gray-100 px-4 py-2">
        <span
          class="flex gap-1.5"
          aria-hidden="true"
        >
          <span class="h-3 w-3 rounded-full bg-gray-300" />
          <span class="h-3 w-3 rounded-full bg-gray-300" />
          <span class="h-3 w-3 rounded-full bg-gray-300" />
        </span>
        <p class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-1 text-base text-gray-800">
          <i
            class="fas fa-lock text-green-800"
            aria-hidden="true"
          />
          <span class="sr-only">Adresse du site :</span>
          <span
            class="truncate"
            data-testid="adresse"
          >{{ scene.navigateur.adresse }}</span>
        </p>
      </div>

      <div class="p-4 md:p-6">
        <p class="text-base font-bold text-blue-900">
          {{ scene.navigateur.onglet }}
        </p>

        <!-- Étape 1 : le formulaire -->
        <form
          v-if="etape === 'formulaire'"
          novalidate
          autocomplete="off"
          data-testid="formulaire"
          @submit.prevent="continuer"
        >
          <h3
            ref="titreEtape"
            tabindex="-1"
            data-focus-depart
            class="mt-2 scroll-mt-32 text-2xl font-bold text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-blue-800"
          >
            {{ scene.formulaire.titre }}
          </h3>
          <p class="mt-1 text-lg text-gray-700">
            {{ scene.formulaire.introduction }}
          </p>

          <!-- Résumé des erreurs, lu en premier après « Continuer » -->
          <div
            v-if="erreurs.length"
            ref="resume"
            tabindex="-1"
            class="mt-4 scroll-mt-32 rounded-xl border-l-8 border-red-700 bg-red-50 p-4 text-lg text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
            data-testid="resume-erreurs"
          >
            <p class="font-bold">
              {{ erreurs.length > 1 ? `${erreurs.length} champs sont à corriger :` : 'Un champ est à corriger :' }}
            </p>
            <ul class="mt-2 list-disc pl-6">
              <li
                v-for="erreur in erreurs"
                :key="erreur.id"
              >
                <a
                  :href="`#champ-${erreur.id}`"
                  class="text-red-800 underline"
                  @click.prevent="allerAuChamp(erreur.id)"
                >{{ erreur.libelle }}</a> : {{ erreur.message }}
              </li>
            </ul>
          </div>

          <div class="mt-4 space-y-5">
            <div
              v-for="champ in scene.formulaire.champs"
              :key="champ.id"
              :data-testid="`groupe-${champ.id}`"
            >
              <!-- Case à cocher : le libellé suit la case -->
              <label
                v-if="champ.type === 'case'"
                class="flex cursor-pointer items-start gap-3 text-lg text-gray-900"
              >
                <input
                  :id="`champ-${champ.id}`"
                  v-model="valeurs[champ.id]"
                  type="checkbox"
                  class="mt-1 h-6 w-6 shrink-0 accent-blue-800"
                  :aria-invalid="messageDe(champ.id) ? 'true' : 'false'"
                  :aria-describedby="messageDe(champ.id) ? `erreur-${champ.id}` : undefined"
                  :data-testid="`champ-${champ.id}`"
                >
                <span>
                  {{ champ.libelle }}
                  <span
                    v-if="champ.obligatoire"
                    aria-hidden="true"
                    class="font-bold text-red-800"
                  >*</span>
                  <span
                    v-if="champ.obligatoire"
                    class="sr-only"
                  >(obligatoire)</span>
                </span>
              </label>

              <template v-else>
                <label
                  :for="`champ-${champ.id}`"
                  class="block text-lg font-bold text-gray-900"
                >
                  {{ champ.libelle }}
                  <span
                    v-if="champ.obligatoire"
                    aria-hidden="true"
                    class="text-red-800"
                  >*</span>
                  <span
                    v-if="champ.obligatoire"
                    class="sr-only"
                  >(obligatoire)</span>
                </label>
                <p
                  v-if="champ.indication"
                  :id="`indication-${champ.id}`"
                  class="text-base text-gray-700"
                >
                  {{ champ.indication }}
                </p>

                <select
                  v-if="champ.type === 'choix'"
                  :id="`champ-${champ.id}`"
                  v-model="valeurs[champ.id]"
                  :class="classesChamp(champ.id)"
                  :aria-invalid="messageDe(champ.id) ? 'true' : 'false'"
                  :aria-describedby="decrit(champ)"
                  :data-testid="`champ-${champ.id}`"
                >
                  <option value="">
                    Choisissez dans la liste
                  </option>
                  <option
                    v-for="option in champ.options"
                    :key="option"
                    :value="option"
                  >
                    {{ option }}
                  </option>
                </select>
                <input
                  v-else
                  :id="`champ-${champ.id}`"
                  v-model="valeurs[champ.id]"
                  type="text"
                  autocomplete="off"
                  spellcheck="false"
                  :class="classesChamp(champ.id)"
                  :aria-invalid="messageDe(champ.id) ? 'true' : 'false'"
                  :aria-describedby="decrit(champ)"
                  :data-testid="`champ-${champ.id}`"
                >
              </template>

              <p
                v-if="messageDe(champ.id)"
                :id="`erreur-${champ.id}`"
                class="mt-1 flex items-start gap-2 text-base font-bold text-red-800"
                :data-testid="`erreur-${champ.id}`"
              >
                <i
                  class="fas fa-circle-exclamation mt-1"
                  aria-hidden="true"
                />
                {{ messageDe(champ.id) }}
              </p>
            </div>
          </div>

          <button
            type="submit"
            class="mt-6 min-h-11 rounded-xl border-2 border-blue-300 bg-blue-100 px-6 py-2 text-lg font-bold text-blue-950 hover:bg-blue-200 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
            data-testid="continuer-formulaire"
          >
            {{ scene.formulaire.bouton }}
          </button>
        </form>

        <!-- Étape 2 : le récapitulatif -->
        <div
          v-else-if="etape === 'recapitulatif'"
          data-testid="recapitulatif"
        >
          <h3
            ref="titreEtape"
            tabindex="-1"
            data-focus-depart
            class="mt-2 scroll-mt-32 text-2xl font-bold text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-blue-800"
          >
            {{ scene.recapitulatif.titre }}
          </h3>
          <p class="mt-1 text-lg text-gray-700">
            {{ scene.recapitulatif.texte }}
          </p>
          <dl class="mt-4 divide-y divide-gray-200 rounded-xl border border-gray-200">
            <div
              v-for="ligne in lignesRecapitulatif"
              :key="ligne.id"
              class="grid gap-1 px-4 py-2 text-lg sm:grid-cols-[14rem_1fr]"
            >
              <dt class="text-gray-700">
                {{ ligne.libelle }}
              </dt>
              <dd class="font-bold text-gray-900">
                {{ ligne.valeur }}
              </dd>
            </div>
          </dl>
          <div class="mt-6 flex flex-wrap gap-3">
            <button
              type="button"
              class="min-h-11 rounded-xl border-2 border-gray-300 bg-white px-6 py-2 text-lg font-bold text-gray-800 hover:bg-gray-50 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
              data-testid="modifier"
              @click="allerA('formulaire')"
            >
              {{ scene.recapitulatif.modifier }}
            </button>
            <button
              type="button"
              class="min-h-11 rounded-xl border-2 border-blue-300 bg-blue-100 px-6 py-2 text-lg font-bold text-blue-950 hover:bg-blue-200 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
              data-testid="envoyer"
              @click="allerA('confirmation')"
            >
              {{ scene.recapitulatif.envoyer }}
            </button>
          </div>
        </div>

        <!-- Étape 3 : la confirmation et le numéro à noter -->
        <div
          v-else
          data-testid="confirmation"
        >
          <h3
            ref="titreEtape"
            tabindex="-1"
            data-focus-depart
            class="mt-2 flex scroll-mt-32 items-center gap-2 text-2xl font-bold text-gray-900 focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-blue-800"
          >
            <i
              class="fas fa-circle-check text-green-800"
              aria-hidden="true"
            />
            {{ scene.confirmation.titre }}
          </h3>
          <p class="mt-1 text-lg text-gray-700">
            {{ scene.confirmation.texte }}
          </p>
          <p
            class="mt-4 inline-block rounded-xl border-2 border-green-700 bg-green-50 px-5 py-3 font-mono text-2xl font-bold tracking-wider text-gray-900"
            data-testid="numero"
          >
            {{ scene.confirmation.numero }}
          </p>

          <form
            class="mt-6 rounded-2xl border-2 border-amber-400 bg-amber-50 p-4"
            novalidate
            autocomplete="off"
            @submit.prevent="verifierNumero"
          >
            <label
              for="numero-recopie"
              class="block text-lg font-bold text-gray-900"
            >
              {{ scene.confirmation.question }}
            </label>
            <input
              id="numero-recopie"
              v-model="numero"
              type="text"
              autocomplete="off"
              spellcheck="false"
              :class="classesChamp(erreurNumero ? 'numero' : null)"
              :aria-invalid="erreurNumero ? 'true' : 'false'"
              :aria-describedby="erreurNumero ? 'erreur-numero' : undefined"
              data-testid="champ-numero"
            >
            <p
              v-if="erreurNumero"
              id="erreur-numero"
              class="mt-1 text-base font-bold text-red-800"
              data-testid="erreur-numero"
            >
              {{ scene.confirmation.erreur }}
            </p>
            <button
              type="submit"
              class="mt-4 min-h-11 rounded-xl border-2 border-blue-300 bg-blue-100 px-6 py-2 text-lg font-bold text-blue-950 hover:bg-blue-200 focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800"
              data-testid="valider-numero"
            >
              Valider
            </button>
          </form>
        </div>
      </div>
    </section>

    <!-- Annonces pour les lecteurs d'écran -->
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
import { computed, nextTick, ref, watch } from 'vue'
import { numeroRecopie, recapitulatif, valeursDepart, verifierFormulaire } from '@/exercices/moteur/moteurFormulaire'
import { amenerEnVue } from '../defilement'

const props = defineProps({
  scene: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['reussie'])

// Saisies en mémoire seulement : rien n'est envoyé ni enregistré
const etape = ref('formulaire')
const valeurs = ref(valeursDepart(props.scene.formulaire))
const erreurs = ref([])
const numero = ref('')
const erreurNumero = ref(false)
const annonce = ref('')
const titreEtape = ref(null)
const resume = ref(null)

const lignesRecapitulatif = computed(() => recapitulatif(props.scene.formulaire, valeurs.value))

// Un champ corrigé perd son message tout de suite ; les nouveaux messages n'apparaissent qu'à « Continuer »
watch(valeurs, () => {
  if (erreurs.value.length === 0) return
  const restantes = new Set(verifierFormulaire(props.scene.formulaire, valeurs.value).map((erreur) => erreur.id))
  erreurs.value = erreurs.value.filter((erreur) => restantes.has(erreur.id))
}, { deep: true })

const messageDe = (id) => erreurs.value.find((erreur) => erreur.id === id)?.message ?? null

const decrit = (champ) => [
  champ.indication ? `indication-${champ.id}` : null,
  messageDe(champ.id) ? `erreur-${champ.id}` : null,
].filter(Boolean).join(' ') || undefined

const classesChamp = (id) => [
  'mt-1 block w-full max-w-md rounded-lg border-2 bg-white px-3 py-2 text-lg text-gray-900',
  'focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-blue-800',
  id && (id === 'numero' || messageDe(id)) ? 'border-red-700' : 'border-gray-400',
]

const allerA = async (suivante) => {
  etape.value = suivante
  annonce.value = ''
  await nextTick()
  amenerEnVue(titreEtape.value, { focus: true, bloc: 'start' })
}

const continuer = async () => {
  erreurs.value = verifierFormulaire(props.scene.formulaire, valeurs.value)
  if (erreurs.value.length === 0) {
    await allerA('recapitulatif')
    return
  }
  annonce.value = `${erreurs.value.length} ${erreurs.value.length > 1 ? 'champs sont' : 'champ est'} à corriger.`
  await nextTick()
  amenerEnVue(resume.value, { focus: true, bloc: 'start' })
}

const allerAuChamp = (id) => {
  amenerEnVue(document.getElementById(`champ-${id}`), { focus: true })
}

const verifierNumero = () => {
  if (numeroRecopie(props.scene.confirmation, numero.value)) {
    emit('reussie')
    return
  }
  erreurNumero.value = true
  annonce.value = props.scene.confirmation.erreur
}
</script>
