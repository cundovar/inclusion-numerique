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
  <div class="hidden xl:flex xl:w-full xl:fixed xl:top-0 xl:right-0 xl:z-50 xl:justify-between xl:items-center xl:px-8 backdrop-blur-md bg-white/30 py-4">
    <!-- Logo DevDoc -->
    <div class="flex items-center gap-2rounded-lg px-4 py-2 ">
   
      <span class="text-gray-800 font-bold text-xl">DevDoc</span>
    </div>
    
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
        class="search-results absolute top-24 right-1/2 translate-x-1/2 m-auto p-4 max-h-[40rem] max-w-full max-md:hidden bg-gray-100 w-96 rounded-xl my-4 overflow-y-auto shadow-lg z-50"
      >
        <div class="flex justify-between items-center mb-2">
          <div class="text-sm text-gray-600 font-medium">
            {{ searchResults.length }} résultat(s) trouvé(s)
          </div>
          <i
              @click="closeSearchResults"
              class="text-blue-950 hover:bg-red-200 hover:text-red-600 p-2 rounded-full pi pi-times cursor-pointer text-xl transition-colors"
            ></i>
        </div>
        <ul class="space-y-2">
          <li
            v-for="menu in searchResults"
            :key="menu.page.slug"
            class="hover:bg-pink-200 p-3 border-b border-gray-200 rounded-lg transition-colors duration-200"
          >
            <router-link
              :to="`/pages${menu.page.slug}`"
              class="block hover:underline"
              @click="search = ''; searchResults = []"
            >
              <div class="font-medium text-gray-800">
                {{ capitalize(menu.title) }}
              </div>
              <div class="text-sm text-gray-600 mt-1">
                {{ menu.category?.name || 'Catégorie non définie' }}
              </div>
              <div v-if="menu.content" class="text-xs text-gray-500 mt-1 truncate">
                {{ menu.content.substring(0, 100) }}...
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
      class="border-blue-950 bg-[var(--primary-color)] flex fixed w-full z-50 h-20 shadow-xl justify-between items-center p-5 top-0 xl:hidden"
    >
      <div @click="toggleMenu" class="cursor-pointer">
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
      
      <!-- Logo centré -->
      <div class="flex-1 max-md:absolute top-0 left-0 flex justify-center">
        <div class="flex items-center gap-2">
          <i class="pi pi-code text-blue-950" style="font-size: 1.8rem"></i>
          <span class="text-blue-950 font-bold text-lg">DevDoc</span>
        </div>
      </div>

      <!-- Boutons à droite -->
      <div class="flex  items-center gap-2">
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
        <router-link to="/profile">
        <button class="p-2 text-2xl text-gray-800 cursor-pointer flex items-center gap-2  transition-all duration-200 hover:shadow-xl">
          <i class="pi pi-user"></i>
       
        </button>
      </router-link>
        <button 
          @click="logout"
          class="cursor-pointer text-red-600 hover:text-red-800 flex items-center gap-1 px-2 py-1 rounded hover:bg-red-100 transition-colors"
        >
          <i class="text-2xl pi pi-sign-out"></i>
        
        </button>
      </div>

    
    </div>

    <nav
      @mouseleave="isXlOuPlus && (hoveredCategory = null)"
      :class="[
        'md:p-5    h-screen xl:top-16 fixed z-10 max-md:z-50 max-xl:top-20 transition-transform duration-300 ease-in-out',
        isXlOuPlus
          ? 'flex w-1/6  flex-col translate-x-0'
          : isMenuOpen
          ? 'w-full bg-gray-200 shadow-black pl-2 pr-2 pb-96 shadow-2xl md:top-20 translate-x-0 max-h-[calc(100vh-80px)] max-md:h-[calc(100vh-80px)] max-md:overflow-y-auto flex flex-col max-md:z-50'
          : 'max-lg:w-full lg:w-1/2  -translate-x-full md:-translate-x-[60rem] lg:-translate-x-[70rem] ',
      ]"
    >
    
    
    
    
    
 
      <a class="md:hidden  bg-gray-500 gap-2 flex items-center justify-start p-1 hover:bg-gray-600" href="https://github.com/poleS-dev" target="_blank">
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

  <lienMDN/>

  
      <div
     @click="!islgOuPlus && (openMenu(cat.name))"
        class="cursor-pointer  flex  relative flex-col  justify-center"
        v-for="cat in cats"
        :key="cat.id"
        @mouseenter="islgOuPlus && (hoveredCategory = cat.name)"

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
          
        <div class=" xl:absolute xl:top-0 xl:-right-[26rem] max-h-[20rem] xl:pr-10 xl:pl-10 xl:w-[25rem] rounded-xl bg-gray-200 div-scrollbar overflow-y-auto">


          <!-- menu des cours -->
          <div
            class="md:gap-y-1 "
            v-if="(islgOuPlus && hoveredCategory === cat.name) || (!islgOuPlus && clickMenu === cat.name)"
            v-for="group in menusByCategory[cat.name]"
            :key="group.label"
          >   
            <div
              class="p-2 md:w-2/3 md:mt-1 md:border-b border-green-950 mb-2  md:text-start md:bg-transparent md:text-black max-md:border-b max-md:hover:bg-indigo-200 max-md:border-b-blue-300 max-md:border-t-blue-300 max-md:bg-indigo-100 max-md:text-xl max-md:border-t"
            > 
              {{ group.label }} :
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
        class="flex  w-full md:justify-start justify-end items-center max-md:hidden"
      >

      <div>

        <h3>documentations</h3>
        <list_logo/>

      </div>
   
      </div>

     
  
    </nav>

   

   <!-- {# <list_logo/> #} -->
    
    <!-- resultat de la recherche en mobile -->
    <div
        v-if="modalIsOpen && searchResults.length"
        class="fixed inset-0 overflow-hidden backdrop-blur-2xl bg-opacity-50 z-50 flex items-center justify-center p-4 md:hidden"
      >
      <div class="bg-white rounded-lg w-full max-w-md max-h-96 overflow-hidden shadow-xl">
        <div class="flex justify-between items-center p-4 border-b">
          <h2 class="text-lg font-bold">Résultats de recherche</h2>
          <i
            @click="closeModal"
            class="text-blue-950 pi pi-times cursor-pointer text-xl"
          ></i>
        </div>
        <div class="p-2 text-sm text-gray-600 border-b">
          {{ searchResults.length }} résultat(s) trouvé(s)
        </div>
        <ul class="overflow-y-auto max-h-80 p-2">
          <li
            v-for="menu in searchResults"
            :key="menu.page.slug"
            class="hover:bg-pink-200 p-3 border-b border-gray-200 transition-colors duration-200"
          >
            <router-link
              :to="`/pages${menu.page.slug}`"
              class="block hover:underline"
              @click="closeModal(); search = ''; searchResults = []"
            >
              <div class="font-medium text-gray-800">
                {{ capitalize(menu.title) }}
              </div>
              <div class="text-sm text-gray-600 mt-1">
                {{ menu.category?.name || 'Catégorie non définie' }}
              </div>
              <div v-if="menu.content" class="text-xs text-gray-500 mt-1 truncate">
                {{ menu.content.substring(0, 80) }}...
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
      class=" xl:p-20   max-md:z-10 max-md:w-full h-auto xl:w-4/5 xl:ml-16 xl:self-center right-0 overflow-hidden"
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



// Filtrage dynamique
const launchSearch = () => {
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

  console.log("menus", menus.value);

  const query = search.value.toLowerCase();
  
  // Recherche approfondie dans tous les contenus
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

  cats.value.forEach((cat) => {
    const grouped = {};

    menus.value
      .filter((menu) => menu.category.name === cat.name)
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

    result[cat.name] = Object.values(grouped);
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
