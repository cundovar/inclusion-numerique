<template>
  <div  id="app2" class="relative w-full xl:w-4/5 m-auto ">
    <div class="h-20"></div>
    <div  class="  bg-[var(--primary-color)]  flex max-md:fixed w-full z-50  h-20 shadow-xl justify-between items-center  p-5 md:hidden">
       <div @click="toggleMenu" class="cursor-pointer">
         <i v-if="!isMenuOpen" class="  pi pi-bars cursor-pointer " style="font-size: 1.5rem"></i>
         <i v-else class="pi pi-times  cursor-pointer" style="font-size: 1.5rem"></i>

       </div>
      <inputSearch v-model="search" @search="launchSearch" placeholder="Rechercher" />

      
      </div>
  
   
    
    <nav
    @mouseleave="isMdOuPlus && (hoveredCategory = null)"
  :class="[
    '   md:p-5 xl:w-1/5 z-10',
    isMdOuPlus ? 'fixed flex  flex-col md:w-1/4' : (isMenuOpen ? 'flex bg-[var(--primary-color)] pb-96 fixed top-20 left-0 right-0    max-h-[calc(100vh-56px)]  overflow-y-auto flex-col w-full max-md:z-50' : 'hidden')
  ]">
<div class="md:hidden  relative flex justify-end items-end self-end ">

  </div>

  <p class="p-2  bg-pink-400 text-end cursor-pointer" @click="logout">deconnexion</p>

      <div
        class=" cursor-pointer flex flex-col justify-center "
        v-for="cat in cats"
        :key="cat.id"
       
       @mouseenter="isMdOuPlus && (hoveredCategory = cat.name)"
        
      >

    
  
        <h1 class="text-2xl pt-2 hover:bg-blue-400 md:mt-2 max-md:text-center uppercase max-md:bg-blue-200 bg-blue-300 pb-2 pl-1 ">{{ cat.name }}</h1>
   
        <div class="  right-0  overflow-hidden">
          
          <div
            
         v-if="isMdOuPlus && hoveredCategory === cat.name || !isMdOuPlus"
            class="   "
            v-for="group in menusByCategory[cat.name]"
            :key="group.label"
          >
          <div class="p-2 border-b hover:bg-indigo-200  border-b-blue-300 border-t-blue-300 bg-indigo-100  md:text-center text-xl border-t ">

            {{ group.label }}
          </div>
            
            <button class=" w-full  flex hover:bg-gray-300  flex-col gap-2" v-for="menu in group.items" :key="menu.page.slug">
              <router-link :to="`/pages${menu.page.slug}`"  @click="toggleMenu" class=" focus:bg-gray-300 focus:text-white z-50 hover:text-blue-600">
              <p class="p-2 md:text-white hover:text-fuchsia-900  max-md:text-start max-md:ml-1 "> -- {{ menu.title }}</p>
              </router-link>
            </button>
          </div>
        
        
        
        </div>
      </div>
      <div class="flex  mt-10 w-full  justify-end items-center   max-md:hidden ">
        <inputSearch v-model="search" @search="launchSearch" placeholder="Rechercher" />
      </div>


      <div v-if="searchResults.length" class="p-4 bg-gray-100  w-96rounded-xl my-4">
  <h2 class="text-lg font-bold mb-2">Résultats de la recherche :</h2>
  <ul>
    <li v-for="menu in searchResults" :key="menu.page.slug" class=" hover:bg-pink-200 p-1 border-b">
      <router-link :to="`/pages${menu.page.slug}`" class="hover:underline  p-2">
        {{ menu.title }}
      </router-link>
    </li>
  </ul>
</div>

<div v-else-if="search.value" class="italic text-gray-500 my-4">
  Aucun résultat trouvé.
</div>
    </nav>

    <main class="md:w-3/4 self-end  max-md:z-10 max-md:w-full h-auto xl:w-4/5  right-0  overflow-hidden">
   
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed,onUnmounted } from "vue";
import axios from "axios";
 import inputSearch from "./views/components/inputSearch.vue";

const cats = ref([]);
const menus = ref([]);
const hoveredCategory = ref(null);
const isMenuOpen = ref(false);
const isMdOuPlus = ref(window.innerWidth >= 768); // md = 768px en Tailwind
const search = ref("");
const searchResults = ref([]);



 const logout = () => {
   axios.post("/logout");
 };
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
  console.log(isMenuOpen.value);
};

const fetchMenus = async () => {
  try {
    const [response, responses2] = await Promise.all([
      axios.get("/api/categories"),
      axios.get("/api/page_contents"),
    ]);

    cats.value = response.data.member;
    menus.value = responses2.data.member;

    console.log("Categories:", cats.value);
    console.log("Menus:", menus.value);
  } catch (error) {
    console.error("Erreur lors de la récupération des menus:", error);
  }
};

const updateIsMdOrAbove = () => {
  isMdOuPlus.value = window.innerWidth >= 768;
  console.log(isMdOuPlus.value);
};
onMounted(() => {
  fetchMenus();
  window.addEventListener("resize", updateIsMdOrAbove);
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

  console.log("menus",  menus.value);

  const query = search.value.toLowerCase();

  searchResults.value = menus.value.filter((menu) => {
    const title = menu.title?.toLowerCase() || '';
    const content = menu.content?.toLowerCase() || '';
    const label = menu.menu.label?.toLowerCase() || '';
    const code = menu.code?.toLowerCase() || '';
    const slug = menu.page.slug?.toLowerCase() || '';
    const category = menu.category.name?.toLowerCase() || '';

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
            items: []
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
nav {
  margin-top: 10px;
  scroll-behavior: smooth;
}


</style>
