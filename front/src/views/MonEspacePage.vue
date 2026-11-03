<template>
  <div class="min-h-screen px-4 pb-40 pt-6 md:px-10 xl:pt-0">
    <div class="mx-auto max-w-5xl">
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
            Mon espace
          </li>
        </ol>
      </nav>

      <p
        v-if="state === 'loading'"
        class="mt-10 text-xl text-gray-700"
        role="status"
      >
        Chargement de votre espace…
      </p>

      <!-- Pas connecté -->
      <section
        v-else-if="state === 'anonymous'"
        class="mt-10 rounded-2xl border border-blue-200 bg-blue-50 p-6"
        data-testid="espace-anonyme"
      >
        <h1 class="text-3xl font-bold text-gray-900">
          Mon espace
        </h1>
        <p class="mt-3 text-xl text-gray-700">
          Connectez-vous pour retrouver les cours et exercices que votre médiateur a préparés pour vous.
          Les autres cours restent ouverts à tous, sans compte.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
          <a
            href="/login"
            class="rounded-xl border-2 border-blue-300 bg-blue-100 px-5 py-3 text-lg font-semibold text-blue-950 hover:bg-blue-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700"
          >Se connecter</a>
          <a
            href="/register"
            class="rounded-xl border-2 border-gray-300 bg-white px-5 py-3 text-lg font-semibold text-gray-800 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700"
          >Créer un compte</a>
        </div>
      </section>

      <p
        v-else-if="state === 'offline'"
        class="mt-10 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-xl text-gray-800"
        role="status"
      >
        Votre espace n'est pas disponible sans connexion internet. Réessayez quand vous serez connecté.
      </p>

      <template v-else-if="state === 'ready'">
        <header class="mt-6">
          <div class="flex items-center gap-4">
            <span
              class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-gray-900 md:h-16 md:w-16"
              aria-hidden="true"
            >
              <i class="fas fa-user text-2xl md:text-3xl" />
            </span>
            <h1 class="text-3xl font-bold text-gray-900 md:text-4xl">
              Bonjour {{ espace.pseudo }}
            </h1>
          </div>
          <p class="mt-2 text-xl text-gray-700">
            Retrouvez ici ce que votre médiateur a préparé pour vous. Les autres cours restent accessibles depuis l'accueil.
          </p>
        </header>

        <p
          v-if="isEmpty"
          class="mt-8 rounded-2xl border border-gray-200 bg-gray-50 p-6 text-xl text-gray-700"
          data-testid="espace-vide"
        >
          Rien n'a encore été préparé pour vous. Donnez votre pseudo, <strong>{{ espace.pseudo }}</strong>, à votre médiateur :
          il pourra vous réserver des cours et des exercices.
        </p>

        <!-- Mes cours -->
        <section
          v-if="espace.cours.length"
          class="mt-10"
          aria-labelledby="mes-cours"
        >
          <h2
            id="mes-cours"
            class="text-2xl font-bold text-gray-900"
          >
            Mes cours
          </h2>
          <ul
            class="mt-4 grid gap-4 md:grid-cols-2"
            data-testid="mes-cours"
          >
            <li
              v-for="cours in espace.cours"
              :key="cours.id"
            >
              <router-link
                :to="`/pages/${cours.slug}`"
                class="block h-full rounded-2xl border border-blue-200 bg-blue-50 p-5 hover:bg-blue-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700"
              >
                <span class="text-xl font-semibold text-gray-900">{{ cours.titre }}</span>
                <span
                  v-if="cours.parcours || cours.duree"
                  class="mt-1 block text-base text-gray-700"
                >
                  {{ [cours.parcours, cours.duree].filter(Boolean).join(' · ') }}
                </span>
              </router-link>
            </li>
          </ul>
        </section>

        <!-- Mes parcours -->
        <section
          v-if="espace.parcours.length"
          class="mt-10"
          aria-labelledby="mes-parcours"
        >
          <h2
            id="mes-parcours"
            class="text-2xl font-bold text-gray-900"
          >
            Mes parcours
          </h2>
          <ul class="mt-4 grid gap-4 md:grid-cols-2">
            <li
              v-for="parcours in espace.parcours"
              :key="parcours.id"
            >
              <router-link
                :to="`/category/${encodeURIComponent(parcours.nom)}`"
                class="flex h-full items-start gap-4 rounded-2xl border border-green-200 bg-green-50 p-5 hover:bg-green-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700"
              >
                <img
                  v-if="parcours.illustrationUrl"
                  :src="parcours.illustrationUrl"
                  alt=""
                  class="h-16 w-16 shrink-0 rounded-xl object-cover"
                >
                <span>
                  <span class="block text-xl font-semibold text-gray-900">{{ parcours.nom }}</span>
                  <span
                    v-if="parcours.description"
                    class="mt-1 block text-base text-gray-700"
                  >{{ parcours.description }}</span>
                </span>
              </router-link>
            </li>
          </ul>
        </section>

        <!-- Mes exercices -->
        <section
          v-if="espace.exercices.length"
          class="mt-10"
          aria-labelledby="mes-exercices"
        >
          <h2
            id="mes-exercices"
            class="text-2xl font-bold text-gray-900"
          >
            Mes exercices
          </h2>
          <p class="mt-2 text-lg text-gray-700">
            {{ espace.exercices.length }} {{ espace.exercices.length > 1 ? 'questions préparées' : 'question préparée' }} pour vous.
          </p>
          <router-link
            :to="{ name: 'questionQCM', params: { index: 0 }, query: { perso: '1' } }"
            class="mt-4 inline-block rounded-xl border-2 border-amber-300 bg-amber-100 px-5 py-3 text-lg font-semibold text-gray-900 hover:bg-amber-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700"
            data-testid="commencer-exercices"
          >
            Commencer mes exercices
          </router-link>
        </section>

        <!-- Mon compte -->
        <section
          class="mt-12 border-t border-gray-200 pt-8"
          aria-labelledby="mon-compte"
        >
          <h2
            id="mon-compte"
            class="text-2xl font-bold text-gray-900"
          >
            Mon compte
          </h2>
          <p class="mt-2 text-lg text-gray-700">
            Pseudo : <strong>{{ espace.pseudo }}</strong>
            <template v-if="espace.email">
              <br>Adresse de connexion : <strong>{{ espace.email }}</strong>
            </template>
          </p>

          <form
            class="mt-4 max-w-md space-y-3"
            @submit.prevent="changePassword"
          >
            <h3 class="text-xl font-semibold text-gray-900">
              Changer de mot de passe
            </h3>
            <label class="block text-lg text-gray-800">
              Mot de passe actuel
              <input
                v-model="password.current"
                type="password"
                autocomplete="current-password"
                required
                class="mt-1 block w-full rounded-lg border-2 border-gray-300 px-3 py-2"
              >
            </label>
            <label class="block text-lg text-gray-800">
              Nouveau mot de passe (8 caractères au moins)
              <input
                v-model="password.next"
                type="password"
                autocomplete="new-password"
                minlength="8"
                required
                class="mt-1 block w-full rounded-lg border-2 border-gray-300 px-3 py-2"
              >
            </label>
            <button
              type="submit"
              class="rounded-xl border-2 border-blue-300 bg-blue-100 px-5 py-2 text-lg font-semibold text-blue-950 hover:bg-blue-200"
            >
              Enregistrer
            </button>
            <p
              v-if="password.message"
              :class="password.ok ? 'text-green-800' : 'text-red-700'"
              role="status"
            >
              {{ password.message }}
            </p>
          </form>

          <form
            class="mt-10 max-w-md space-y-3 rounded-2xl border border-red-200 bg-red-50 p-5"
            @submit.prevent="deleteAccount"
          >
            <h3 class="text-xl font-semibold text-gray-900">
              Supprimer mon compte
            </h3>
            <p class="text-lg text-gray-700">
              Votre compte et l'accès aux contenus préparés pour vous sont supprimés définitivement.
              Les cours ouverts à tous restent accessibles sans compte.
            </p>
            <label class="block text-lg text-gray-800">
              Mot de passe, pour confirmer
              <input
                v-model="deletion.password"
                type="password"
                autocomplete="current-password"
                required
                class="mt-1 block w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2"
              >
            </label>
            <button
              type="submit"
              class="rounded-xl border-2 border-red-300 bg-white px-5 py-2 text-lg font-semibold text-red-800 hover:bg-red-100"
            >
              Supprimer définitivement
            </button>
            <p
              v-if="deletion.message"
              class="text-red-700"
              role="alert"
            >
              {{ deletion.message }}
            </p>
          </form>
        </section>
      </template>

      <!-- Ouvert à tous, avec ou sans compte : les cours publics se lisent aussi hors ligne -->
      <TelechargementHorsLigne v-if="state !== 'loading'" />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { clearAuthenticatedClientState } from '../services/auth.js'
import { useData } from '../utils/fetchDataPwa'
import TelechargementHorsLigne from '../components/features/horsLigne/TelechargementHorsLigne.vue'

const { clearUser } = useData()
const state = ref('loading')
const espace = ref({ pseudo: '', email: null, cours: [], parcours: [], exercices: [] })
const password = reactive({ current: '', next: '', message: '', ok: false })
const deletion = reactive({ password: '', message: '' })

const isEmpty = computed(() => !espace.value.cours.length && !espace.value.parcours.length && !espace.value.exercices.length)

const errorMessage = (error, fallback) => error.response?.data?.message ?? fallback

onMounted(async () => {
  try {
    const { data } = await axios.get('/user-api/mon-espace')
    espace.value = data
    state.value = 'ready'
  } catch (error) {
    state.value = error.response ? 'anonymous' : 'offline'
  }
})

const changePassword = async () => {
  password.message = ''
  try {
    const { data } = await axios.post('/user-api/mon-espace/mot-de-passe', {
      motDePasseActuel: password.current,
      nouveauMotDePasse: password.next,
    })
    Object.assign(password, { current: '', next: '', message: data.message, ok: true })
  } catch (error) {
    Object.assign(password, { message: errorMessage(error, 'Le mot de passe n’a pas pu être modifié.'), ok: false })
  }
}

const deleteAccount = async () => {
  deletion.message = ''
  if (!window.confirm('Supprimer définitivement votre compte ?')) {
    return
  }
  try {
    await axios.delete('/user-api/mon-espace', { data: { motDePasse: deletion.password } })
    await clearUser()
    clearAuthenticatedClientState()
    window.location.assign('/spa')
  } catch (error) {
    deletion.message = errorMessage(error, 'Le compte n’a pas pu être supprimé.')
  }
}
</script>
