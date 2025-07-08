<template>
  <div
    v-if="user.roles.includes('ROLE_USER')"
    id="app2"
    class="relative w-full xl:w-4/5 m-auto"
  >
    <div class="h-20"></div>
    <div
      class="bg-[var(--primary-color)] flex max-md:fixed w-full z-50 h-20 shadow-xl justify-between items-center p-5 md:hidden"
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
        href="http://localhost:8080/page-content"
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
        'md:p-5 xl:w-1/5 fixed z-10 max-md:z-50 max-md:top-20 transition-transform duration-300 ease-in-out',
        isMdOuPlus
          ? 'flex   flex-col w-1/4 translate-x-0'
          : isMenuOpen
          ? 'w-full shadow-black pl-2 pr-2 shadow-2xl translate-x-0  max-h-[calc(100vh-56px)]  overflow-y-auto flex-col max-md:z-50'
          : 'w-full -translate-x-full',
      ]"
    >
    <p class="p-2  bg-amber-200 max-md:hidden cursor-pointer"> bonjour <span class="font-bold"> {{ user.username }}</span></p>
      <div class="md:hidden relative flex justify-end items-end self-end"></div>
      <a class="md:hidden  bg-gray-500 gap-2 flex items-center justify-start p-1 hover:bg-gray-600" href="https://github.com/poleS-dev" target="_blank">
        <i class="pi pi-github cursor-pointer   text-amber-200 right-1/2 top-5" style="font-size:2rem"></i>
       <p class="text-amber-200  font-bold"> GITHUB </p>
    </a>
      <p class=" font-bold max-md:text-xl p-2 max-md:bg-pink-200 bg-pink-400 text-center cursor-pointer" @click="logout">
        deconnexion
      </p>
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
        v-if=" searchResults.length"
        class="p-4 max-md:hidden bg-gray-100 w-96rounded-xl my-4"
      >
        <h2 class="text-lg font-bold mb-2">Résultats de la recherche :</h2>
        <ul>
          <li
            v-for="menu in searchResults"
            :key="menu.page.slug"
            class="hover:bg-pink-200 p-1 border-b"
          >
            <router-link
              :to="`/pages${menu.page.slug}`"
              class="hover:underline p-2"

            >
            
              {{ capitalize(menu.title) }}
            </router-link>
          </li>
        </ul>
      </div>

      <div v-else-if="search.value" class="italic text-gray-500 my-4">
        Aucun résultat trouvé.
      </div>
    </nav>
    <a
      v-if="user.roles.includes('ROLE_ADMIN')"
      target="_blank"
      href="http://localhost:8080/page-content"
      class="cursor-pointer absolute text-amber-200 right-5 top-5"
    >
      admin
    </a>
    
    
    <h2 class="absolute text-amber-200   top-5 right-5" v-else="user.roles.includes('ROLE_USER')"> visiteur</h2>
    
    <list_logo/>
    
    <!-- resultat de la recherche en mobile -->
    <div
        v-if="modalIsOpen && searchResults.length"
        class="p-4  md:hidden bg-gray-100 modal w-96rounded-xl my-4"
      >
      <i
          @click="closeModal"
          class="text-blue-950 pi pi-times float-end cursor-pointer"
          style="font-size: 1.5rem"
        ></i>
        <h2 class="text-lg font-bold mb-2">Résultats de la recherche :</h2>
        <ul>
          <li
            v-for="menu in searchResults"
            :key="menu.page.slug"
            class="hover:bg-pink-200 p-1 border-b"
          >
            <router-link
              :to="`/pages${menu.page.slug}`"
              class="hover:underline p-2"
            >
              {{ capitalize(menu.title) }}
            </router-link>
          </li>
        </ul>
      </div>

      <div v-else-if="search.value" class="italic text-gray-500 my-4">
        Aucun résultat trouvé.
      </div>



    <main
      class="md:w-3/4 self-end max-md:z-10 max-md:w-full h-auto xl:w-4/5 right-0 overflow-hidden"
    >
      <router-view />
    </main>
  </div>

  <div v-else>
    <h1>Vous n'avez pas accès à cette page</h1>
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


const cats = ref([]);
const menus = ref([]);
const hoveredCategory = ref(null);
const isMenuOpen = ref(false);
const isMdOuPlus = ref(window.innerWidth >= 768); // md = 768px en Tailwind
const search = ref("");
const searchResults = ref([]);
const user = ref({ username: "", roles: [] });
const modalIsOpen = ref(true);
const isMobile=ref(window.innerWidth < 768);
const clickMenu=ref(null);
  


orderMenuTitle(menus.value, 'title', true);

// --- DB IndexedDB pour PWA ---
const dbPromise = openDB('spa-db', 1, {
  upgrade(db) {
    db.createObjectStore('menus', { keyPath: 'id' });
    db.createObjectStore('categories', { keyPath: 'id' });
    db.createObjectStore('user', { keyPath: 'username' });
  }
});
async function saveMenusToDB(menusArray) {
  const db = await dbPromise;
  const tx = db.transaction('menus', 'readwrite');
  
  menusArray.forEach(item => {
    const pureItem = JSON.parse(JSON.stringify(item)); 
    tx.store.put(pureItem);
  });
  
  await tx.done;
}



async function saveCatsToDB(catsArray) {
  const db = await dbPromise;
  const tx = db.transaction('categories', 'readwrite');
  // convertion en données json
  catsArray.forEach(item => {
    const pureItem = JSON.parse(JSON.stringify(item)); 
    tx.store.put(pureItem);
  });
  
  await tx.done;
}

async function saveUserToDB(user) {
  const db = await dbPromise;
  // convertion en données json
  const tx = db.transaction('user', 'readwrite');
  const jsonUser = JSON.parse(JSON.stringify(user)); 
  tx.store.put(jsonUser);
  await tx.done;
}

// --- Chargement user dans IndexedDB pour le offline ---

async function loadUserFromDB() {
  const db = await dbPromise;
  const user = await db.get('user', 1);
  return user;
}

async function loadMenusFromDB() {
  const db = await dbPromise;
  return db.getAll('menus');
}

async function loadCatsFromDB() {
  const db = await dbPromise;
  return db.getAll('categories');
}





const fetchUser = async () => {
  try {
    const response = await axios.get(" /user-api/me");
    user.value = response.data;
    console.log("User:", user.value);
    // --- Stockage user dans IndexedDB pour le offline ---
    await saveUserToDB(user.value);

  } catch (error) {
   if(!error.response){
    // pas de repopose alors surement or connection
    console.warn("Pas de reponse, surement en offline");
    user.value = await loadUserFromDB();
    console.log("User offline:", user.value);
   }else if(error.response.status === 401){
    console.error("Erreur lors de la récupération de l'utilisateur", error);
    router.push("/login");
   }else{
    console.error("autre error", error);
   
   }
  }
};
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

//menus
const fetchMenus = async () => {
  try {
    const [response, responses2] = await Promise.all([
      axios.get("/api/categories"),
      axios.get("/api/page_contents"),
    ]);

    cats.value = response.data.member;
    menus.value = responses2.data.member;

    // --- Stockage dans IndexedDB pour le offline ---
    await saveMenusToDB(menus.value);
    await saveCatsToDB(cats.value);

    console.log("Categories:", cats.value);
    console.log("Menus:", menus.value);
  } catch (error) {
    console.error("Erreur lors de la récupération des menus:", error);

    // Chargement offline
    cats.value = await loadCatsFromDB();
    menus.value = await loadMenusFromDB();

    console.log("Categories offline:", cats.value);
    console.log("Menus offline:", menus.value);
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
  window.addEventListener("resize", updateIsMdOrAbove);
  updateIsMobile()
  console.log("true");
  fetchUser();
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
  //recherche
  searchResults.value = menus.value.filter((menu) => {
    const title = menu.title?.toLowerCase() || "";
    const content = menu.content?.toLowerCase() || "";
    const label = menu.menu.label?.toLowerCase() || "";
    const code = menu.code?.toLowerCase() || "";
    const slug = menu.page.slug?.toLowerCase() || "";
    const category = menu.category.name?.toLowerCase() || "";

    return (
      title.includes(query) ||
      content.includes(query) ||
      label.includes(query) ||
      code.includes(query) ||
      slug.includes(query) ||
      category.includes(query)
    );
  });
};
// close modal
const closeModal = () => {
  modalIsOpen.value = false;
  
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
