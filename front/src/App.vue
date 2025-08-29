<template>
   <pagesFrame/>
  
  <!-- Pop-up de téléchargement d'app mobile -->
  <div 
    v-if="showMobileAppPopup" 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 mx-4 max-w-sm w-full shadow-xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Télécharger l'app</h3>
        <button 
          @click="closeMobileAppPopup"
          class="text-gray-400 hover:text-gray-600 text-xl"
        >
          ×
        </button>
      </div>
      
      <div class="text-center">
        <p class="text-gray-600 mb-4">
          Téléchargez notre application mobile pour une meilleure expérience !
        </p>
        
        <div class="flex flex-col gap-3">
          <button 
            @click="installPWA"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
          >
            <i class="pi pi-download"></i>
            Installer l'application
          </button>
          
          <a 
            :href="currentOrigin" 
            class="bg-green-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-green-700 transition-colors"
          >
            <i class="pi pi-external-link"></i>
            Ouvrir dans le navigateur
          </a>
        </div>
      </div>
    </div>
  </div>

  <div
    
    id="app2"
    class="relative w-full xl:w-full m-auto"
  >


  <!-- Header bureau avec logo, recherche et déconnexion sur la même ligne -->
  <div class="  hidden xl:flex xl:w-full  xl:fixed  xl:top-0 xl:right-0 xl:z-50 xl:justify-between xl:items-center xl:px-8 backdrop-blur-md bg-white/30 py-4">
    <!-- Logo DevDoc -->
    <Router-link to="/">


      <div class="flex gap-5  items-center gap-2rounded-lg px-4 py-2 ">
        <i class="pi pi-code text-blue-950" style="font-size: 1.8rem"></i>
        
        <span class="text-gray-800 font-bold text-xl">DevDoc</span>
        <i class="pi pi-code text-blue-950" style="font-size: 1.8rem"></i>
      </div>

    </Router-link>
    
    <!-- Recherche au centre -->
    <inputSearch
      v-model="search"
      @search="launchSearch"
      placeholder="Rechercher"
      class="w-96"
    />
    
    <!-- Boutons profil et déconnexion à droite -->
    <div class="flex items-center gap-2">
      <!-- Bouton profil -->
      <router-link to="/profile">
        <button class="p-2 bg-amber-200 hover:bg-amber-300 text-gray-800 cursor-pointer flex items-center gap-2 rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
          <i class="pi pi-user"></i>
          <span class="font-medium">{{ user.username }}</span>
        </button>
      </router-link>
      
      <!-- Bouton déconnexion -->
      <button 
        @click="logout"
        class="p-2 bg-pink-400 hover:bg-pink-500 text-white cursor-pointer flex items-center gap-2 rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl"
      >
        <i class="pi pi-sign-out"></i>
        <span class="font-medium">Déco</span>
      </button>
    </div>
  </div>

        <div
        v-if="searchResults.length"
        class="search-results absolute top-24 right-1/2 translate-x-1/2 m-auto p-4 max-h-[40rem] max-w-full max-md:hidden bg-white w-[28rem] rounded-xl my-4 overflow-y-auto shadow-xl z-50 border border-gray-200"
      >
        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-200">
          <div class="text-sm font-semibold text-gray-700">
            <span class="text-blue-600">{{ searchResults.length }}</span> résultat(s) trouvé(s)
            <span v-if="searchAnalysis" class="text-xs text-gray-500 block mt-1">
              Recherche IA • {{ searchAnalysis.intent || 'Analyse en cours' }}
            </span>
          </div>
          <i
              @click="closeSearchResults"
              class="text-gray-400 hover:bg-red-100 hover:text-red-600 p-2 rounded-full pi pi-times cursor-pointer text-lg transition-colors"
            ></i>
        </div>

        <!-- Tags d'analyse IA -->
        <div v-if="searchAnalysis && (searchAnalysis.keywords || searchAnalysis.technologies)" class="mb-3">
          <div class="flex flex-wrap gap-1">
            <span v-for="keyword in searchAnalysis.keywords?.slice(0, 3)" 
                  :key="keyword"
                  class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
              {{ keyword }}
            </span>
            <span v-for="tech in searchAnalysis.technologies?.slice(0, 2)" 
                  :key="tech"
                  class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">
              {{ tech }}
            </span>
          </div>
        </div>

        <ul class="space-y-3">
          <li
            v-for="result in searchResults"
            :key="result.id || result.page?.slug"
            class="hover:bg-gray-50 p-3 border border-gray-100 rounded-lg transition-all duration-200 hover:shadow-md"
          >
            <router-link
              :to="`/pages${result.page?.slug || result.pageSlug}`"
              class="block"
              @click="search = ''; searchResults = []; searchAnalysis = null"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="font-medium text-gray-800 text-sm leading-tight pr-2">
                  {{ capitalize(result.title) }}
                </div>
                <span :class="`${result.scoreColor || 'bg-gray-500'} text-white text-xs px-2 py-1 rounded-full font-medium flex-shrink-0`">
                  {{ result.relevanceScore || 0 }}%
                </span>
              </div>
              
              <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                <span class="bg-gray-100 px-2 py-1 rounded">
                  {{ result.category?.name || result.category || 'Non catégorisé' }}
                </span>
                <span v-if="result.menu?.label" class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                  {{ result.menu.label }}
                </span>
                <span v-if="result.hasCode" class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded flex items-center">
                  <i class="pi pi-code mr-1"></i>Code
                </span>
              </div>
              
              <!-- Summary avec highlight -->
              <div v-if="result.displaySummary || result.summary" 
                   class="text-xs text-gray-600 leading-relaxed bg-gray-50 p-2 rounded border-l-2 border-blue-200">
                {{ result.displaySummary || result.summary }}
              </div>
              
              <!-- Date si disponible -->
              <div v-if="result.formattedDate" class="text-xs text-gray-400 mt-2 flex items-center">
                <i class="pi pi-calendar mr-1"></i>
                {{ result.formattedDate }}
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <div v-else-if="search && search.trim()" class="italic text-gray-500 my-4 max-md:hidden">
        Aucun résultat trouvé pour "{{ search }}".
      </div>
    



    <div class="h-20"></div>
    <div
      class="border-blue-950  pb-2 bg-[var(--primary-color)] flex fixed w-full z-50 h-20 shadow-xl justify-between items-center  top-0 xl:hidden"
      >
      <!-- Logo centré -->
      <Router-link to="/">
      <div class="flex-1 max-xl:absolute top-0  left-0 flex justify-center">
        <div class="flex items-center gap-2">
          <i class="pi pi-code text-blue-950" style="font-size: 1.8rem"></i>
          <span class="text-blue-950 font-bold text-xl">DevDoc</span>
        </div>
      </div>
      </Router-link>

      <!-- Bouton menu burger -->
      <div @click="toggleMenu" class="cursor-pointer ml-1 left-3 absolute md:left-5  mt-10">
        <i
          v-if="!isMenuOpen"
          class="text-blue-950 pi pi-bars cursor-pointer"
          style="font-size: 1.5rem"
        ></i>
        <i
          v-else
          class="pi pi-times cursor-pointer"
          style="font-size: 1.5rem"
        ></i>
      </div>
      

      <!-- Boutons à droite -->
      <div class="  max-xl:w-5/6  flex max-xl:mt-10 items-center gap-2">
        <a
          v-if="user.roles.includes('ROLE_ADMIN')"
          target="_blank"
          :href="APP_CONFIG.ADMIN_URL"
          class="cursor-pointer text-amber-900 px-2 py-1 rounded text-sm"
        >
          <i class=" text-2xl pi pi-cog"></i>
        </a>
        <inputSearch
        v-model="search"
        @search="launchSearch"
        placeholder="Rechercher"
        class="flex-1 xl:hidden   "
      />
    
        <button 
          @click="logout"
          class="cursor-pointer ml-2 text-red-600 hover:text-red-800 flex items-center gap-1 px-2 py-1 rounded hover:bg-red-100 transition-colors"
        >
          <i class="text-2xl pi pi-sign-out"></i>
        
        </button>
      </div>

    
    </div>
<div 
  :class="[
    'scroll-gauche xl:top-10 min-h-screen fixed border-blue-950 overflow-y-scroll div-scrollbar transition-all duration-500 ease-in-out',
    isXlOuPlus ? '' : isMenuOpen ? 'max-xl:w-full md:w-2/3 z-50 top-20 ' : 'w-full -translate-x-full  top-20',
    hoveredCategory ? 'z-[100000] w-1/2 ' : 'z-0 w-[20rem]'
  ]"
>

  <nav
    @mouseleave="isXlOuPlus && (hoveredCategory = null)"
    :class="[
      'md:p-5  float-left z-50 h-screen  max-md:z-50 max-xl:top-20 transition-transform duration-300 ease-in-out',
      isXlOuPlus
        ? 'flex xl:w-[16rem]  2xl:w-[20rem] flex-col translate-x-0'
        : isMenuOpen
        ? 'w-full  pl-2 pr-2 bt  bg-gray-200  md:top-20 translate-x-0 max-h-[calc(100vh-80px)] max-md:h-[calc(100vh-80px)] max-md:overflow-y-auto flex flex-col max-md:z-50'
        : 'max-lg:w-full lg:w-1/2  -translate-x-full md:-translate-x-[60rem] lg:-translate-x-[70rem] ',
    ]"
  >
  
  
  
  
  

    <a class="md:hidden border max-xl:border-b-gray-50  bg-gray-500 gap-2 flex items-center justify-start p-1 hover:bg-gray-600" href="https://github.com/poleS-dev" target="_blank">
      <i class="pi pi-github cursor-pointer   text-amber-200 right-1/2 top-5" style="font-size:2rem"></i>
     <p class="text-amber-200  font-bold"> GITHUB </p>
 
    </a>

    <!-- Bouton de test temporaire -->
   <!--
    #  <button 
        @click="showMobileAppPopup = true" 
        class="bg-red-500 text-white p-2 m-2 rounded"
      >
        Test Popup
      </button> #}
  
  
  -->
<!-- list de lien mdn pour mobile dans nav  -->




    <div
   @click="!isXlOuPlus && (openMenu(cat.name))"
      class="cursor-pointer  flex  relative flex-col  justify-center"
      v-for="cat in cats"
      :key="cat.id"
      @mouseenter="isXlOuPlus && (hoveredCategory = cat.name)"

      >
      <!-- pour rendre jaune le menu selectionné -->
      <h1
              :class="{
  'bg-yellow-200': (islgOuPlus && hoveredCategory === cat.name) || (!islgOuPlus && clickMenu === cat.name)
}"

        class="text-2xl max-md:text-xl max-md:drop-shadow-xl max-md:drop-shadow-gray-400 max-md:border-b max-md:border-gray-400 pt-2 hover:bg-blue-400 md:mt-2 max-md:text-center uppercase max-md:bg-blue-200 bg-blue-300 pb-2 pl-1"
      >
        <button class="cursor-pointer max-md:w-full max-md:h-full  max-md:focus:text-white max-md:bg-blue-200 max-md:text-center  focus:bg-blue-500">{{ cat.name }}</button>
      </h1>
        
      <div class="xl:absolute xl:top-0 xl:-right-[26rem] xl:z-[99999] xl:shadow-lg max-h-[20rem] xl:pr-10 xl:pl-10 xl:w-[25rem] rounded-xl bg-gray-200 div-scrollbar overflow-y-auto">


        <!-- menu des cours -->
        <div
          class="md:gap-y-1 "
          v-if="(isXlOuPlus && hoveredCategory === cat.name) || (!isXlOuPlus && clickMenu === cat.name)"
          v-for="group in menusByCategory[cat.name]"
          :key="group.label"
        >   
          <div
            class="p-2 md:w-2/3 md:mt-1 md:border-b border-green-950 mb-2  md:text-start md:bg-transparent md:text-black max-md:border-b max-md:hover:bg-indigo-200 max-md:border-b-blue-300 max-md:border-t-blue-300 max-md:bg-indigo-100 max-md:text-xl max-md:border-t"
          > 
            {{ group.label }} 
          </div>

          <button
            class=" w-full md:rounded-sm shadow-2xl border border-indigo-800  mb-2 xl:ml-2 flex hover:bg-gray-300 bg-gray-100 flex-col gap-2"
            v-for="menu in group.items"
            :key="menu.page.slug"
          >
            <router-link
              :to="`/pages${menu.page.slug}`"
              @click="toggleMenu"
              class="focus:bg-gray-400   focus:text-white z-50 hover:text-blue-600"
            >
          
              <p
                class="p-2  md:text-blue-500 hover:text-fuchsia-900 flex justify-around max-md:justify-start items-center max-md:text-start "
              >
              <div class="flex  justify-center items-center w-1/3">
                <i
                
                  class="pi pi-code max-md:text-blue-600 md:text-yellow-600"
                ></i>
                </div>
                
                <div class="flex-1 text-center border-blue-300 shadow border flex justify-center items-center">
                  {{ capitalize(menu.title) }}
                </div>
                <div class="flex  justify-center items-center w-1/3">
                  <i
                    class="pi pi-code max-md:text-blue-600 md:text-yellow-600"
                  ></i>
                  </div>
              </p>
            </router-link>
          </button>
        </div>



      </div>
    </div>
    <div
        class="text-2xl max-md:text-xl xl:hidden max-md:drop-shadow-xl pb-2 mt-4 pl-1"
      >
      <SelectfunctionVocabulaire @closeMenu="closeMobileMenu"/>
      <router-link
      to="/qcm"
      @click="toggleMenu"
      class="text-xl  h-[3rem] cursor-pointer mt-4 shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="fas fa-brain"></i>
      QCM IA
    </router-link>
     
      </div>
    <div
      class="flex  w-full md:justify-start justify-end items-center "
    >

    <div class=" max-xl:mt-2 mb-96 max-xl:bg-amber-300   max-xl:p-3 gap-y-5 flex flex-col  max-xl:w-full ">

      <h3>documentations : </h3>
      <list_logo/>

    </div>

    
  </div>
 <!-- <vracMain/> --> 


   

  </nav>
  
</div>

   

   <!-- {# <list_logo/> #} -->
    
    <!-- resultat de la recherche en mobile -->
    <div
        v-if="modalIsOpen && searchResults.length"
        class="fixed inset-0 overflow-hidden backdrop-blur-2xl bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 md:hidden"
      >
      <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-hidden shadow-xl">
        <div class="flex justify-between items-center p-4 border-b bg-blue-50">
          <div>
            <h2 class="text-lg font-bold text-gray-800">Résultats IA</h2>
            <div class="text-sm text-gray-600">
              <span class="text-blue-600 font-medium">{{ searchResults.length }}</span> résultat(s)
            </div>
          </div>
          <i
            @click="closeModal"
            class="text-gray-400 hover:text-red-600 pi pi-times cursor-pointer text-xl p-2 hover:bg-red-100 rounded-full transition-colors"
          ></i>
        </div>

        <!-- Tags d'analyse IA mobile -->
        <div v-if="searchAnalysis && (searchAnalysis.keywords || searchAnalysis.technologies)" class="p-3 border-b bg-gray-50">
          <div class="flex flex-wrap gap-1">
            <span v-for="keyword in searchAnalysis.keywords?.slice(0, 2)" 
                  :key="keyword"
                  class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
              {{ keyword }}
            </span>
            <span v-for="tech in searchAnalysis.technologies?.slice(0, 1)" 
                  :key="tech"
                  class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">
              {{ tech }}
            </span>
          </div>
          <div v-if="searchAnalysis.intent" class="text-xs text-gray-600 mt-1">
            {{ searchAnalysis.intent }}
          </div>
        </div>

        <ul class="overflow-y-auto max-h-[60vh] p-2">
          <li
            v-for="result in searchResults"
            :key="result.id || result.page?.slug"
            class="hover:bg-gray-50 p-3 border-b border-gray-100 transition-colors duration-200"
          >
            <router-link
              :to="`/pages${result.page?.slug || result.pageSlug}`"
              class="block"
              @click="closeModal(); search = ''; searchResults = []; searchAnalysis = null"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="font-medium text-gray-800 text-sm pr-2">
                  {{ capitalize(result.title) }}
                </div>
                <span :class="`${result.scoreColor || 'bg-gray-500'} text-white text-xs px-2 py-1 rounded-full font-medium flex-shrink-0`">
                  {{ result.relevanceScore || 0 }}%
                </span>
              </div>
              
              <div class="flex items-center gap-1 text-xs text-gray-600 mb-2 flex-wrap">
                <span class="bg-gray-100 px-2 py-1 rounded">
                  {{ result.category?.name || result.category || 'Non catégorisé' }}
                </span>
                <span v-if="result.hasCode" class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
                  Code
                </span>
              </div>
              
              <!-- Summary mobile -->
              <div v-if="result.displaySummary || result.summary" 
                   class="text-xs text-gray-600 leading-relaxed bg-blue-50 p-2 rounded border-l-2 border-blue-300">
                {{ (result.displaySummary || result.summary).substring(0, 120) }}{{ (result.displaySummary || result.summary).length > 120 ? '...' : '' }}
              </div>
            </router-link>
          </li>
        </ul>
      </div>
    </div>

    <div v-else-if="modalIsOpen && search && search.trim()" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 md:hidden">
      <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-bold">Recherche</h2>
          <i
            @click="closeModal"
            class="text-blue-950 pi pi-times cursor-pointer text-xl"
          ></i>
        </div>
        <div class="italic text-gray-500 text-center">
          Aucun résultat trouvé pour "{{ search }}".
        </div>
      </div>
    </div>



    <main
      class=" xl:p-20     max-md:z-10 max-md:w-full h-auto xl:w-4/5 xl:ml-16 xl:self-center right-0 overflow-hidden"
    >
      <router-view />
      <AfertLogin v-if="$route.path === '/'" />
    </main>

    <navFooterMobil @click="closed"  />

  </div>


</template>

<script setup>
import { ref, onMounted, computed, onUnmounted,  } from "vue";
import axios from "axios";
import router from "./router";
import inputSearch from "./views/components/inputSearch.vue";
import { openDB } from 'idb';
import { scrollToTop } from "./utlis/domUtils";
import { capitalize } from "./utlis/stringsUtlis";
import { orderMenuTitle } from "./utlis/arrayUtills";
import list_logo from "./views/components/list_logo.vue";
import { logos } from "./tab-object/list-logo";
import lienMDN from "./views/components/menuMobile/lienMDN.vue";
import navFooterMobil from "./views/components/NavFooterMobil/Main.vue";
import {useData} from "./utlis/fetchDataPwa";
import pagesFrame from "./views/components/MenuPageFramwork/PagesFrame.vue";
import { APP_CONFIG } from "./config/app.js";
import AfertLogin from "./views/components/AfertLogin.vue";
import vracMain from "./views/components/vrac/vracMain.vue";
import IntelligentSearchService from "./services/intelligentSearchService.js";
import SelectfunctionVocabulaire from './views/components/MenuPageFramwork/SelectfunctionVocabulaire.vue'



const hoveredCategory = ref(null);
const isMenuOpen = ref(false);
const isMdOuPlus = ref(window.innerWidth >= 768); // md = 768px en Tailwind
const islgOuPlus = ref(window.innerWidth >= 1024); // lg = 1024px en Tailwind
const isXlOuPlus = ref(window.innerWidth >= 1280); // xl = 1280px en Tailwind
const search = ref("");
const searchResults = ref([]);

const modalIsOpen = ref(false);
const isMobile=ref(window.innerWidth < 768);
const clickMenu=ref(null);
const searchAnalysis = ref(null);
const { menus, user,cats, fetchMenus, fetchUser } = useData()

// Pop-up mobile app
const showMobileAppPopup = ref(false);
let deferredPrompt = null;
const currentOrigin = ref(window.location.origin);
  








//deconnexion
const logout = () => {
  axios.post("/logout").then(() => {
    window.location.href = "/login";
  });
};
//menu
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
  console.log(isMenuOpen.value);
};
//close menu en mobile
const closeMobileMenu = () => {
  isMenuOpen.value = false;
}

// openmenu en mobile
const openMenu = (catName) => {
  if (clickMenu.value === catName) {
    clickMenu.value = null;
  } else {
    clickMenu.value = catName;
  }
};
// close modal search mobile
const closeModal = () => {
  modalIsOpen.value = false;
  console.log("ddddd");
  
};

// close search results desktop
const closeSearchResults = () => {
  search.value = '';
  searchResults.value = [];
  searchAnalysis.value = null;
};

// fermeture de modal et menu en mobile

const closed=()=>{
  closeModal();
  closeMobileMenu();
}




const updateScreenSizes = () => {
  isMobile.value = window.innerWidth < 768;
  isMdOuPlus.value = window.innerWidth >= 768;
  islgOuPlus.value = window.innerWidth >= 1024;
  isXlOuPlus.value = window.innerWidth >= 1280;
  
  // Fermer le menu mobile quand on passe en desktop
  if (isXlOuPlus.value && isMenuOpen.value) {
    isMenuOpen.value = false;
  }
};
onMounted(() => {
  fetchMenus();
  fetchUser();
  window.addEventListener("resize", updateScreenSizes);
  updateScreenSizes();
  
  // Écouter l'événement beforeinstallprompt pour PWA
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
  });
  
  // Vérifier si le pop-up mobile app doit être affiché avec un délai
  setTimeout(() => {
    checkMobileAppPopup();
  }, 500);
  
  console.log("true");
});
onUnmounted(() => {
  window.removeEventListener("resize", updateScreenSizes);
  console.log("false");
});



// Recherche intelligente avec IA
const launchSearch = async () => {
  if (!search.value.trim()) {
    searchResults.value = [];
    modalIsOpen.value = false;
    return;
  }
  
  // Sécurité : ne pas rechercher si moins de 2 caractères
  if (search.value.trim().length < 2) {
    searchResults.value = [];
    modalIsOpen.value = false;
    return;
  }

  try {
    // Vérifier si le service de recherche intelligente est disponible
    const isServiceAvailable = await IntelligentSearchService.checkServiceHealth();
    
    if (isServiceAvailable) {
      // Utiliser la recherche intelligente avec IA
      console.log("Recherche intelligente activée");
      const result = await IntelligentSearchService.search(search.value, 15);
      
      if (result.success) {
        searchResults.value = IntelligentSearchService.formatResults(result.results);
        searchAnalysis.value = result.analysis; // Stocker l'analyse pour affichage
        console.log("Analyse IA:", result.analysis);
        console.log(`${result.total} résultats trouvés avec IA`);
      } else {
        throw new Error("Service de recherche IA non disponible");
      }
    } else {
      throw new Error("Service de recherche IA non accessible");
    }
  } catch (error) {
    console.warn("Recherche IA échouée, utilisation de la recherche classique:", error.message);
    
    // Fallback : recherche classique
    const query = search.value.toLowerCase();
    
    searchResults.value = menus.value.filter((menu) => {
      const title = menu.title?.toLowerCase() || "";
      const content = menu.content?.toLowerCase() || "";
      const label = menu.menu?.label?.toLowerCase() || "";
      const code = menu.code?.toLowerCase() || "";
      const slug = menu.page?.slug?.toLowerCase() || "";
      const category = menu.category?.name?.toLowerCase() || "";
      const type = menu.type?.toLowerCase() || "";
      
      // Recherche aussi dans les pageBlocks si disponibles
      let blockContent = "";
      if (menu.pageBlocks && Array.isArray(menu.pageBlocks)) {
        blockContent = menu.pageBlocks.map(block => {
          const blockTitle = block.title?.toLowerCase() || "";
          const blockContent = block.content?.toLowerCase() || "";
          const blockCode = block.code?.toLowerCase() || "";
          return blockTitle + " " + blockContent + " " + blockCode;
        }).join(" ");
      }
      
      // Recherche dans toutes les propriétés disponibles
      const searchableText = [
        title,
        content,
        label,
        code,
        slug,
        category,
        type,
        blockContent
      ].join(" ");
      
      return searchableText.includes(query);
    });
    
    // Trier les résultats par pertinence (titre en premier, puis contenu)
    searchResults.value.sort((a, b) => {
      const aTitle = a.title?.toLowerCase() || "";
      const bTitle = b.title?.toLowerCase() || "";
      
      if (aTitle.includes(query) && !bTitle.includes(query)) return -1;
      if (!aTitle.includes(query) && bTitle.includes(query)) return 1;
      
      return 0;
    });
  }
  
  // Ouvrir la modal pour mobile ou scroll pour desktop
  if (searchResults.value.length > 0) {
    if (isMobile.value) {
      modalIsOpen.value = true;
    } else {
      setTimeout(() => {
        const resultsElement = document.querySelector('.search-results');
        if (resultsElement) {
          resultsElement.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest' 
          });
        }
      }, 100);
    }
  }
};


// Fonctions pour le pop-up mobile app
const checkMobileAppPopup = () => {
  // Vérifier si le pop-up a déjà été affiché
  const hasSeenPopup = localStorage.getItem('mobileAppPopupShown');
  
  
  // Afficher seulement si pas encore vu et si on est sur mobile
  if (!hasSeenPopup && isMobile.value) {
    setTimeout(() => {
      showMobileAppPopup.value = true;
    }, 1000);
  }
  
  
};
const fltererdmenus=["wordpress","vue","react"]

const closeMobileAppPopup = () => {
  showMobileAppPopup.value = false;
  // Marquer comme vu dans localStorage
  localStorage.setItem('mobileAppPopupShown', 'true');
};

// Fonction pour installer la PWA
const installPWA = async () => {
  // Détecter iOS
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
  
  if (deferredPrompt) {
    // Android/Chrome - Installation automatique
    deferredPrompt.prompt();
    
    const { outcome } = await deferredPrompt.userChoice;
    
    if (outcome === 'accepted') {
      console.log('PWA installée');
      closeMobileAppPopup();
    }
    
    deferredPrompt = null;
  } else if (isIOS) {
    // iOS - Instructions spécifiques
    alert('Pour installer sur iOS :\n1. Appuyez sur le bouton "Partager" 📤\n2. Sélectionnez "Ajouter à l\'écran d\'accueil" 📱\n3. Confirmez en appuyant sur "Ajouter"');
  } else {
    // Autres navigateurs
    alert('Pour installer l\'application, utilisez le menu de votre navigateur : "Ajouter à l\'écran d\'accueil"');
  }
};

const menusByCategory = computed(() => {
  const result = {};
  const filteredCategories = ["react", "reactjs", "wordpress","symfony", "docker", "vuejs", "vue"]; // Catégories à exclure de la nav
  const filteredLabels = ["react", "reactjs", "wordpress","symfony", "docker", "vuejs", "vue"]; // Labels/menus à exclure de la nav

  cats.value
    .filter((cat) => !filteredCategories.includes(cat.name.toLowerCase().trim()))
    .forEach((cat) => {
      const grouped = {};

      menus.value
        .filter((menu) => menu.category.name === cat.name)
        .filter((menu) => !filteredLabels.includes(menu.menu.label?.toLowerCase().trim()))
        .forEach((menu) => {
          const label = menu.menu.label;
          if (!grouped[label]) {
            grouped[label] = {
              label: label,
              items: [],
            };
          }
          grouped[label].items.push(menu);
        });

      result[cat.name] = Object.values(grouped)
    });

  return result;
});
</script>

<style scoped>
/* 
*{

  border: 1px solid red;
} */


nav {
  margin-top: 10px;
  scroll-behavior: smooth;
}

.div-scrollbar {
  /* Firefox */
  scrollbar-width: thin;
  scrollbar-color: 	#778899 transparent;
}

/* Chrome, Edge, Safari */
.div-scrollbar::-webkit-scrollbar {
  width: 4px;
  border-radius: 4px;
}

.div-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.div-scrollbar::-webkit-scrollbar-thumb {
  background-color: #87cefa;
  border-radius: 10px;
  box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.2);
}

.div-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #555; /* tu peux mettre une variation de ta couleur principale */
}
</style>
