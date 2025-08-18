<template>
      <div
    class=" hidden xl:block xl:h-screen pb-96 max-xl:hidden overflow-y-auto  xl:items-end xl:w-[16rem] xl:pt-0 xl:pr-2  xl:justify-start  max-xl:right-10 max-xl:gap-5 sm:flex-row sm:top-0 sm:m-auto  sm:absolute sm:items-start sm:pt-20 xl:gap-2  xl:fixed xl:top-20 xl:right-0 xl:z-50 xl:flex xl:flex-col max-xl:items-end "
  >

    <router-link
      v-for="tech in techs"
      :key="tech"
      :to="`/pages/${tech}`"
      class=" text-xl xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer   shadow-neutral-600  bg-blue-300 p-2 hover:bg-blue-400 text-gray-600 font-bold hover:underline "
    >
      {{ tech }}
    </router-link>
    
    <!-- Lien vers le QCM -->
    <router-link
      to="/qcm"
      class="text-xl xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="fas fa-brain"></i>
      QCM IA
    </router-link>
    
    <SelectfunctionVocabulaire/>
  </div>

</template>

<script setup>
import SelectfunctionVocabulaire from './SelectfunctionVocabulaire.vue'
const techs = ['symfony', 'vuejs', 'reactjs', 'wp']

</script>


    