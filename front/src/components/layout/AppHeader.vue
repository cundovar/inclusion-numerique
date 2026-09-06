<template>
  <div
    :class="[
      header.bgColor || 'backdrop-blur-md bg-white/30',
      'hidden xl:flex xl:w-full xl:fixed xl:top-0 xl:right-0 xl:z-50 xl:justify-between xl:items-center xl:px-8 py-4'
    ]"
  >
    <!-- Logo DevDoc -->
    <router-link to="/">
      <div
        :class="[
          header.hoverColor,
          'flex gap-5 items-center rounded-lg px-4 py-2'
        ]"
      >

        <i
          :class="[header.textColor || 'text-blue-950', 'pi pi-code']"
          style="font-size: 1.8rem"
        ></i>
        <span
          :class="[header.textColor || 'text-gray-800', 'font-bold text-xl']"
        >
         {{ siteName }}
        </span>
        <i
          :class="[header.textColor || 'text-blue-950', 'pi pi-code']"
          style="font-size: 1.8rem"
        ></i>
      </div>
    </router-link>

    <!-- Recherche au centre -->
    <SearchInput
      :modelValue="search"
      @update:modelValue="$emit('update:search', $event)"
      @search="$emit('search')"
      placeholder="Rechercher"
      class="w-96"
    />

    <!-- Boutons profil admin selecte niveau et déconnexion à droite -->
    <div class="flex items-center gap-2">
      <!-- =================================================================
           SÉLECTEUR DE NIVEAU - Filtre global pour toute l'application
           =================================================================
           - v-model="selectedNiveau" : lié au store Pinia (réactif)
           - :options="levels" : liste des niveaux depuis l'API
           - Quand l'utilisateur change le niveau ici, toute l'app se met à jour:
             * AppNavigation (menu gauche)
             * HomePage (liste des catégories)
             * CategoryPage (cours de la catégorie)
           - Le niveau est persisté dans localStorage (reste après refresh)
      -->
      <!-- Sélecteur de niveau stylisé -->
      <select
        v-model="selectedLevel"
        class="w-48 h-9 px-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-700
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               hover:border-gray-400 cursor-pointer transition-all duration-200"
      >
        <option
          v-for="level in levels"
          :key="level.value"
          :value="level.value"
        >
          {{ level.name }}
        </option>
      </select>

      <a
        v-if="isAdmin"
        :href="APP_CONFIG.ADMIN_URL"
        target="_blank"
        rel="noopener noreferrer"
      >
        <AppButton
          variant="outline"
          size="sm"
          icon="pi pi-cog"
          text-content="Backoffice"
        />
      </a>
      <AuthButton />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import SearchInput from '../features/search/SearchInput.vue';
import AuthButton from '../features/auth/AuthButton.vue';
import { useCustomization } from '../../composables/ui/useCustomization';
import AppButton from '../ui/AppButton.vue';
import { useData } from '../../utils/fetchDataPwa';
import { APP_CONFIG } from '../../config/app.js';
import AppSelect from '../ui/AppSelect.vue';

// =============================================================================
// STORE NIVEAU - Connexion au filtre global
// =============================================================================
import { useNiveauStore } from '@/stores/niveauCoursStore';
import { storeToRefs } from 'pinia';

// Initialisation du store
const niveauStore = useNiveauStore()

// Extraction des refs réactives du store:
// - niveaux: liste des niveaux disponibles depuis l'API (Junior, Senior, etc.)
// - selectedNiveau: le niveau actuellement sélectionné (ou null pour "tous")
const { niveaux, selectedNiveau } = storeToRefs(niveauStore)

// Liste des niveaux avec "Tous les niveaux" en première option
// On utilise une option au lieu du placeholder car el-select a un bug d'affichage
// Note: on stocke l'objet niveau complet pour avoir accès à l'ordre dans le filtrage
const levels = computed(() => [
  { name: 'Tous les niveaux', value: '' },
  ...(niveaux.value || []).map(n => ({ name: n.name, value: n.name, ordre: n.ordre }))
])

// Computed pour convertir null <-> "" pour el-select
// On stocke l'objet niveau complet (avec ordre) dans le store
const selectedLevel = computed({
  get() {
    return selectedNiveau.value?.name || ''  // objet.name ou "" si null
  },
  set(value) {
    if (value === '') {
      selectedNiveau.value = null
    } else {
      // Trouver l'objet niveau complet pour stocker name ET ordre
      const niveau = niveaux.value.find(n => n.name === value)
      selectedNiveau.value = niveau || null
    }
  }
})

// Charger les niveaux au montage du composant
// Note: fetchNiveaux() ne fait rien si les niveaux sont déjà chargés (optimisation)
onMounted(() => {
  niveauStore.fetchNiveaux()
})

// Watch: Réinitialiser selectedNiveau si la valeur persistée n'existe pas dans les options
// Cela corrige le problème du placeholder qui ne s'affiche pas quand une ancienne
// valeur invalide est stockée dans localStorage
watch(levels, (newLevels) => {
  if (niveaux.value.length > 0 && selectedNiveau.value) {
    // selectedNiveau est maintenant un objet avec .name
    const valueExists = newLevels.some(level => level.value === selectedNiveau.value.name)
    if (!valueExists) {
      console.log('⚠️ Niveau sélectionné invalide, réinitialisation:', selectedNiveau.value)
      niveauStore.resetNiveau()
    }
  }
}, { immediate: true })

const { siteName, header } = useCustomization();
const { user } = useData();
const isAdmin = computed(() => user.value?.roles?.includes('ROLE_ADMIN'));






defineProps({
  search: {
    type: String,
    default: ''
  }
});

defineEmits(['update:search', 'search']);
</script>
