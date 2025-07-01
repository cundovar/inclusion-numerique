<template>
  <div @mouseleave="isMdOuPlus && (hoveredCategory = null)" id="app2" class="relative w-full xl:w-4/5 m-auto ">
    <div class="h-20"></div>
    <div @click="toggleMenu" class="  bg-[var(--primary-color)]  flex max-md:fixed w-full z-50  h-20 shadow-xl justify-between items-center  p-5 md:hidden">

      <i v-if="!isMenuOpen" class="  pi pi-bars cursor-pointer " style="font-size: 1.5rem"></i>
      <i v-else class="pi pi-times  cursor-pointer" style="font-size: 1.5rem"></i>
      <div class="text-center">
        les cours
      </div>
      </div>
      <div class=" max-md:hidden text-center">
       <h1 class="text-2xl mb-5"> les cours</h1>
      </div>
    
    <nav
  :class="[
    ' bg-[var(--primary-color)] shadow-xl xl:w-1/5 z-10',
    isMdOuPlus ? 'fixed flex  flex-col md:w-1/4' : (isMenuOpen ? 'flex pb-96 fixed top-20 left-0 right-0    max-h-[calc(100vh-56px)]  overflow-y-auto flex-col w-full max-md:z-50' : 'hidden')
  ]">
<div class="md:hidden  relative flex justify-end items-end self-end ">

  </div>


      <div
        class=" cursor-pointer "
        v-for="cat in cats"
        :key="cat.id"
       
       @mouseenter="isMdOuPlus && (hoveredCategory = cat.name)"
        
      >
  
        <h1 class="text-2xl hover:bg-blue-400 md:mt-2 max-md:bg-blue-200 bg-blue-300 pb-2 pl-1 ">{{ cat.name }}</h1>
   
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
              <p class="p-2 max-md:ml-5 "> {{ menu.title }}</p>
              </router-link>
            </button>
          </div>
        
        
        
        </div>
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

const cats = ref([]);
const menus = ref([]);
const hoveredCategory = ref(null);
const isMenuOpen = ref(false);
const isMdOuPlus = ref(window.innerWidth >= 768); // md = 768px en Tailwind

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
