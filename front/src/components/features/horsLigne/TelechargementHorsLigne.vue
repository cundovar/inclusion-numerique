<template>
  <section
    class="mt-10 rounded-2xl border border-sky-200 bg-sky-50 p-6"
    aria-labelledby="hors-ligne-titre"
    data-testid="telechargement-hors-ligne"
  >
    <h2
      id="hors-ligne-titre"
      class="text-2xl font-bold text-gray-900"
    >
      Lire les cours sans connexion
    </h2>
    <p class="mt-2 text-lg text-gray-700">
      Le texte des cours est enregistré sur cet appareil à chaque visite. Téléchargez aussi les images
      pour tout retrouver en mode avion ou avec une connexion faible.
    </p>

    <p
      v-if="!disponible"
      class="mt-4 text-lg text-gray-700"
    >
      Ce navigateur ne permet pas de garder les images hors ligne.
    </p>

    <template v-else>
      <button
        type="button"
        class="mt-4 rounded-xl border-2 border-sky-300 bg-white px-5 py-3 text-lg font-semibold text-gray-900 hover:bg-sky-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="enCours"
        data-testid="tout-telecharger"
        @click="lancer"
      >
        <i
          class="fas fa-download mr-2"
          aria-hidden="true"
        />
        {{ enCours ? 'Téléchargement…' : 'Tout télécharger pour le hors ligne' }}
      </button>

      <div
        v-if="enCours && total > 0"
        class="mt-4"
      >
        <progress
          class="h-3 w-full"
          :value="fait"
          :max="total"
          aria-label="Progression du téléchargement"
        />
        <p class="mt-1 text-gray-700">
          {{ fait }} / {{ total }}
        </p>
      </div>

      <p
        v-if="message"
        class="mt-4 text-lg"
        :class="erreur ? 'text-red-800' : 'text-gray-800'"
        :role="erreur ? 'alert' : 'status'"
        data-testid="hors-ligne-message"
      >
        {{ message }}
      </p>

      <p
        v-if="dernier"
        class="mt-2 text-gray-700"
      >
        Dernier téléchargement : {{ dernier.toLocaleDateString('fr-FR', { dateStyle: 'long' }) }}
        <span v-if="espace">({{ espace }} utilisés sur l'appareil)</span>
      </p>
    </template>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { dernierTelechargement, espaceUtilise, HorsLigneError, toutTelecharger } from '@/services/telechargementHorsLigne'

const disponible = typeof window !== 'undefined' && 'caches' in window
const enCours = ref(false)
const fait = ref(0)
const total = ref(0)
const message = ref('')
const erreur = ref(false)
const dernier = ref(dernierTelechargement())
const espace = ref('')

const formatOctets = (octets) => (octets >= 1024 * 1024
  ? `${(octets / 1024 / 1024).toFixed(1).replace('.', ',')} Mo`
  : `${Math.max(1, Math.round(octets / 1024))} Ko`)

const majEspace = async () => {
  const octets = await espaceUtilise()
  espace.value = octets ? formatOctets(octets) : ''
}

const lancer = async () => {
  enCours.value = true
  message.value = ''
  erreur.value = false
  try {
    const resultat = await toutTelecharger({
      onProgress: (f, t) => { fait.value = f; total.value = t }
    })
    erreur.value = resultat.echecs > 0
    message.value = erreur.value
      ? `${resultat.cours} cours enregistrés, mais ${resultat.echecs} image(s) n'ont pas pu être téléchargées. Réessayez plus tard.`
      : `${resultat.cours} cours et ${resultat.images} image(s) sont disponibles sans connexion.`
    dernier.value = dernierTelechargement()
    await majEspace()
  } catch (e) {
    erreur.value = true
    message.value = e instanceof HorsLigneError ? e.message : 'Le téléchargement a échoué. Réessayez plus tard.'
  } finally {
    enCours.value = false
  }
}

onMounted(majEspace)
</script>
