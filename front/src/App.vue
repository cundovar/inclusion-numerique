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
 
    <div class="h-20"></div>
    <div
      class="  border-blue-950 bg-[var(--primary-color)] flex max-md:fixed w-full z-50 h-20 shadow-xl justify-between items-center p-5 md:hidden"
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

      <a
        v-if="user.roles.includes('ROLE_ADMIN')"
        target="_blank"
        :href="APP_CONFIG.ADMIN_URL"
        class="cursor-pointer text-amber-900 right-5 top-5"
      >
        adm
      </a>
      <inputSearch
        v-model="search"
        @search="launchSearch"
        placeholder="Rechercher"
      />
    </div>

    <nav
      @mouseleave="isMdOuPlus && (hoveredCategory = null)"
      :class="[
        'md:p-5  fixed z-10 max-md:z-50 max-md:top-20 transition-transform duration-300 ease-in-out',
        isMdOuPlus
          ? 'flex   flex-col translate-x-0'
          : isMenuOpen
          ? 'w-full shadow-black pl-2 pr-2 shadow-2xl translate-x-0  max-h-[calc(100vh-56px)]  overflow-y-auto flex-col max-md:z-50'
          : 'w-full -translate-x-full',
      ]"
    >
    
    
    
    
    
    <div class="md:hidden relative flex justify-end items-end self-end"></div>
    <router-link 
      to="/profile" 
    >
    <p class="p-2  bg-amber-200 hover:bg-amber-300 max-md:hidden cursor-pointer"> <span class="pi pi-user">  </span>  bonjour <span class="font-bold"> {{ user.username }}</span> </p>
    
  </router-link>
      <a class="md:hidden  bg-gray-500 gap-2 flex items-center justify-start p-1 hover:bg-gray-600" href="https://github.com/poleS-dev" target="_blank">
        <i class="pi pi-github cursor-pointer   text-amber-200 right-1/2 top-5" style="font-size:2rem"></i>
       <p class="text-amber-200  font-bold"> GITHUB </p>
   
      </a>
      <p class=" font-bold max-md:text-xl p-2 max-md:bg-pink-200 hover:bg-pink-300 bg-pink-400 text-center cursor-pointer" @click="logout">
        deconnexion
      </p>
      
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
     @click="!isMdOuPlus && (openMenu(cat.name))"
        class="cursor-pointer flex flex-col justify-center"
        v-for="cat in cats"
        :key="cat.id"
        @mouseenter="isMdOuPlus && (hoveredCategory = cat.name)"

      >
        <h1
          class="text-2xl max-md:text-xl max-md:drop-shadow-xl max-md:drop-shadow-gray-400 max-md:border-b max-md:border-gray-400 pt-2 hover:bg-blue-400 md:mt-2 max-md:text-center uppercase max-md:bg-blue-200 bg-blue-300 pb-2 pl-1"
        >
          <button class="cursor-pointer max-md:w-full max-md:h-full max-md:focus:text-white max-md:bg-blue-200 max-md:text-center  focus:bg-blue-500">{{ cat.name }}</button>
        </h1>

        <div class="right-0 md:max-h-[15rem] div-scrollbar overflow-y-auto ">
          <div
            class="md:flex  md:flex-col md:gap-y-1 "
            v-if="(isMdOuPlus && hoveredCategory === cat.name) || (!isMdOuPlus && clickMenu === cat.name)"
            v-for="group in menusByCategory[cat.name]"
            :key="group.label"
          >
            <div
              class="p-2 md:w-2/3 md:mt-1  md:text-start md:bg-transparent md:text-white max-md:border-b max-md:hover:bg-indigo-200 max-md:border-b-blue-300 max-md:border-t-blue-300 max-md:bg-indigo-100 max-md:text-xl max-md:border-t"
            > 
              {{ group.label }} :
            </div>

            <button
              class=" w-full  md:rounded-xl shadow-2xl flex hover:bg-gray-300 bg-gray-100 flex-col gap-2"
              v-for="menu in group.items"
              :key="menu.page.slug"
            >
              <router-link
                :to="`/pages${menu.page.slug}`"
                @click="toggleMenu"
                class="focus:bg-gray-400   focus:text-white z-50 hover:text-blue-600"
              >
                <p
                  class="p-2  md:text-blue-500 hover:text-fuchsia-900 flex justify-around max-md:justify-start items-center max-md:text-start max-md:ml-1"
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
        class="flex mt-10 w-full md:justify-start justify-end items-center max-md:hidden"
      >
        <inputSearch
          v-model="search"
          @search="launchSearch"
          placeholder="Rechercher"
        />
      </div>
<!-- resultat de la recherche en desktop -->
      <div
        v-if="searchResults.length"
        class="search-results p-4 max-md:hidden bg-gray-100 w-96 rounded-xl my-4 max-h-96 overflow-y-auto shadow-lg"
      >
        <div class="mb-2 text-sm text-gray-600 font-medium">
          {{ searchResults.length }} résultat(s) trouvé(s)
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
    </nav>
    <a
      v-if="user.roles.includes('ROLE_ADMIN')"
      target="_blank"
      :href="APP_CONFIG.ADMIN_URL"
      class="cursor-pointer absolute text-amber-200 right-5 top-5"
    >
      admin
    </a>
    
    
    <h2 class="absolute text-amber-200   top-5 right-5" v-else="user.roles.includes('ROLE_USER')"> visiteur</h2>
    
    <list_logo/>
    
    <!-- resultat de la recherche en mobile -->
    <div
        v-if="modalIsOpen && searchResults.length"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 md:hidden"
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
      class="md:w-3/4  xl:p-20 self-end max-md:z-10 max-md:w-full h-auto xl:w-4/5 xl:ml-16 xl:self-center right-0 overflow-hidden"
    >
      <router-view />
      <AfertLogin v-if="$route.path === '/'" />
    </main>

    <navFooterMobil/>
  </div>


</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from "vue";
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
const search = ref("");
const searchResults = ref([]);

const modalIsOpen = ref(true);
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
// openmenu en mobile
const openMenu = (catName) => {
  if (clickMenu.value === catName) {
    clickMenu.value = null;
  } else {
    clickMenu.value = catName;
  }
};



const updateIsMobile=()=>{
  isMobile.value = window.innerWidth < 768;
  console.log( "format mobile",isMobile.value);
}
//update is md ou plus
const updateIsMdOrAbove = () => {
  isMdOuPlus.value = window.innerWidth >= 768;
  console.log("format md ou plus",isMdOuPlus.value);
};
onMounted(() => {
  fetchMenus();
  fetchUser();
  window.addEventListener("resize", updateIsMdOrAbove);
  updateIsMobile();
  
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
  window.removeEventListener("resize", updateIsMdOrAbove);
  console.log("false");
});

// Filtrage dynamique
const launchSearch = () => {
  if (!search.value.trim()) {
    searchResults.value = [];
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
  
  // Scroll automatique vers les résultats sur desktop
  if (searchResults.value.length > 0) {
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
};
// close modal
const closeModal = () => {
  modalIsOpen.value = false;
  
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
