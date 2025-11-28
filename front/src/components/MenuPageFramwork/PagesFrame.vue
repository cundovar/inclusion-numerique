<template>
  <div
  :class="isOpenMenuDroit ? 'translate-x-[45%]' : '-translate-x-0'"
  class=" transition-all duration-500 ease-in-out  xl:block xl:h-screen pb-96 max-xl:hidden overflow-y-auto  xl:items-end xl:w-[16rem] xl:pt-0 xl:pr-2  xl:justify-start  max-xl:right-10 max-xl:gap-5 sm:flex-row sm:top-0 sm:m-auto  sm:absolute sm:items-start sm:pt-20 xl:gap-2  xl:fixed xl:top-20 xl:right-0 xl:z-50 xl:flex xl:flex-col max-xl:items-end "
  >

  <div>

    <ButtonArrowMenu 
    :fonction="toggleMenuDroit" 
  
    :isOpen="!isOpenMenuDroit"
    ajoutClass="absolute left-0 top-0"
    />
    <div  class="h-5"></div>


  </div>
    <router-link
      v-for="tech in catsMenuDroite"
      :key="tech.slug"
      :to="`/category/${tech.name}`"
      class=" text-xl xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer   shadow-neutral-600  bg-blue-300 p-2 hover:bg-blue-400 text-gray-600 font-bold hover:underline "
    >
      {{ tech.name }}
    </router-link>
    
    <!-- Lien vers le QCM -->
    <router-link
      to="/qcm"
      class="text-xl xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="fas fa-brain"></i>
    QCM
    </router-link>
    
    <router-link
      to="/exercices"
      class="text-xl xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer shadow-neutral-600 bg-green-300 p-2 hover:bg-green-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="pi pi-book"></i>
      EXERCICES
    </router-link>
    
    <SelectfunctionVocabulaire/>
  </div>

</template>

<script setup>
import SelectfunctionVocabulaire from './SelectfunctionVocabulaire.vue'
import ButtonArrowMenu from '../commun/button/ButtonArrowMenu.vue'
import { ref, computed } from 'vue'
import { useData } from '../../utlis/fetchDataPwa' 

const { cats, menus } = useData()



const isOpenMenuDroit = ref(false)

function toggleMenuDroit() {
  isOpenMenuDroit.value = !isOpenMenuDroit.value
}
const catsMenuDroite = computed(() =>
  (cats.value || []).filter(
    (cat) => cat.positionMenus?.position === "menu-droite"
  )
);
console.log("catsMenuDroite", catsMenuDroite.value)




</script>


    