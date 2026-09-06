<template>
  <div
    ref="headerRef"
    :class="[
      header.bgColor || 'bg-[var(--primary-color)]',
      'border-blue-950 z-50 py-2 fixed w-full shadow-xl top-0 left-0 xl:hidden'
    ]"
  >
    <!-- Ligne du haut: Menu burger, Logo, Bouton auth -->
    <div class="flex justify-between items-center px-3 py-1">
      <!-- Bouton menu burger -->
      <div @click="$emit('toggleMenu')" class="cursor-pointer">
        <i
          v-if="!isMenuOpen"
          :class="[header.textColor || 'text-blue-950', 'pi pi-bars cursor-pointer']"
          style="font-size: 1.5rem"
        ></i>
        <i
          v-else
          :class="[header.textColor || 'text-blue-950', 'pi pi-times cursor-pointer']"
          style="font-size: 1.5rem"
        ></i>
      </div>

      <!-- Logo centré -->
      <router-link to="/">
        <div class="flex items-center gap-2">
          <i
            :class="[header.textColor || 'text-blue-950', 'pi pi-code']"
            style="font-size: 1.8rem"
          ></i>
          <span
            :class="[header.textColor || 'text-blue-950', 'font-bold text-xl']"
          >
            {{ siteName }}
          </span>
        </div>
      </router-link>

    

    <!-- Bouton backoffice + connexion/déconnexion -->
    <div class="flex items-center gap-2">
      <a
        v-if="isAdmin"
        :href="APP_CONFIG.ADMIN_URL"
        target="_blank"
        rel="noopener noreferrer"
      >
        <AppButton
          variant="outline"
          size="xs"
          icon="pi pi-cog"
          rounded="full"
        />
      </a>
      <AuthButtonMobile />
    </div>
  </div>

    <!-- Ligne du bas: Barre de recherche -->
    <div class="px-3    flex justify-between pt-1 pb-2">
         <!-- Sélecteur de niveau stylisé -->
      <select
        v-model="selectedLevel"
        class="w-48 h-9 px-3  bg-white border border-gray-300 rounded-lg text-sm text-gray-700
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               hover:border-gray-400 cursor-pointer transition-all duration-200"
      >
        <option
          v-for="level in levels"
          :key="level.value"
          :value="level.value"
          ref=""
        >
          {{ level.name }}
        </option>
      </select>

      <AppButton 
      
      rounded="full"
      variant=""
      size="md"
      icon="pi pi-search"
      @click="toggleSearchPanel"
      class="  "
      :class="showSearch ? 'invisible': 'visible'"
      />
<Transition                                                                                                                                                                    
    enter-active-class="transition-all duration-300 ease-out"                                                                                                                    
    enter-from-class="opacity-0 translate-x-full"                                                                                                                                
    enter-to-class="opacity-100 translate-x-0"                                                                                                                                   
    leave-active-class="transition-all duration-300 ease-in"                                                                                                                     
    leave-from-class="opacity-100 translate-x-0"                                                                                                                                 
    leave-to-class="opacity-0 translate-x-full"                                                                                                                                  
  >                                                                                                                                                                              
    <div                                                                                                                                                                         
      v-show="showSearch"                                                                                                                                                        
      class=" bg-sky-50  w-screen  absolute right-0 min-h-screen"                                                                                                                     
    >                                                                                                                                                                            
      <SearchInput                                                                                                                                                               
        :modelValue="search"                                                                                                                                                      
        @update:modelValue="(val)=>search=val"                                                                                                                      
        @search="launchSearch"                                                                                                                                                
        class=" w-[80%] mt-10"                                                                                                                                                     
      />                                                                                                                                                                         
                                                                                                                                                                                 
      

      <AppButton
      class="absolute border cursor-pointer right-0 top-0"
       size="sm"
        textContent="X"
         variant="danger"
       @click="toggleSearchPanel"
          
           />
      

        <!-- Résultats de recherche mobile -->
 
        <ul class="space-y-3 mt-10  flex justify-center flex-col items-center">
          
      <SearchResultItem
        v-for="result in searchResults"
        :key="result.id || result.page?.slug"
        :result="result"
        @close="toggleSearchPanel"
      />
    </ul>
    </div>                                                                                                                                                                       
  </Transition>                                                                                                                                                                  
                        


      

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed,watch} from 'vue';
import Headroom from 'headroom.js';
import AuthButtonMobile from '../features/auth/AuthButtonMobile.vue';
import SearchInput from '../features/search/SearchInput.vue';
import { useCustomization } from '../../composables/ui/useCustomization';
import AppButton from '../ui/AppButton.vue';
import { useData } from '../../utils/fetchDataPwa';
import { APP_CONFIG } from '../../config/app.js';
import SearchResults from '../features/search/SearchResultsMobile.vue';
import SearchResultItem from '../features/search/SearchResultItem.vue';

// =============================================================================
// STORE NIVEAU - Connexion au filtre global
// =============================================================================
import { useNiveauStore } from '@/stores/niveauCoursStore';
import { storeToRefs } from 'pinia';
import { useNavigation } from '@/composables/ui/useNavigation';
import { useSearch } from '@/composables/domain/useSearch';
;
import { useResponsive } from '@/composables/ui/useResponsive';


const { user,menus } = useData();
const { isMobile } = useResponsive()
const {openModal,closeModal,modalIsOpen} = useNavigation()
const { search, searchResults, searchAnalysis, launchSearch, closeSearchResults } = useSearch(menus, user, isMobile, openModal);
// Initialisation du store
const niveauStore = useNiveauStore()

//pour input search
const showSearch = ref(false)

const toggleSearchPanel = () => {
  showSearch.value = !showSearch.value
  if (!showSearch.value) {
    closeSearchResults()
  }
}

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
const {toggleMenu}=useNavigation()
const { menu } = useData();
const isAdmin = computed(() => user.value?.roles?.includes('ROLE_ADMIN'));

defineProps({
  isMenuOpen: {
    type: Boolean,
    default: false
  },

});

defineEmits(['toggleMenu']);

// Headroom.js
const headerRef = ref(null);
let headroom = null;

onMounted(() => {
  if (headerRef.value) {
    headroom = new Headroom(headerRef.value, {
      offset: 100,          // Ne pas activer avant 100px de scroll
      tolerance: {
        up: 10,             // Sensibilité scroll vers le haut
        down: 10            // Sensibilité scroll vers le bas
      },
      classes: {
        initial: 'headroom',
        pinned: 'headroom--pinned',
        unpinned: 'headroom--unpinned',
        top: 'headroom--top',
        notTop: 'headroom--not-top'
      },
      onPin: () => {},
      onUnpin: () => {},
    });
    headroom.init();
  }
});

onUnmounted(() => {
  if (headroom) {
    headroom.destroy();
  }
});
</script>

<style>
/* Headroom.js animations */
.headroom {
  will-change: transform;
  transition: transform 0.3s ease-in-out !important;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

.headroom--pinned {
  transform: translateY(0%) !important;
}

.headroom--unpinned {
  transform: translateY(-100%) !important;
}

.headroom--top {
  transform: translateY(0%) !important;
}

.headroom--not-top.headroom--pinned {
  transform: translateY(0%) !important;
}

.headroom--not-top.headroom--unpinned {
  transform: translateY(-100%) !important;
}
</style>
