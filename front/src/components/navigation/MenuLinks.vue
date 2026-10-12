<template>
  <div class="text-2xl max-md:text-xl xl:hidden max-md:drop-shadow-xl pb-2 mt-4 pl-1">
    <SelectfunctionVocabulaire @close-menu="$emit('closeMenu')" />

    <router-link
      to="/qcm"
      class=" text-lg h-[3rem] cursor-pointer mt-4 shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
      @click="$emit('closeMenu')"
    >
      <i class="fas fa-brain" />
      QCM
    </router-link>

    <router-link
      to="/exercices"
      class=" h-[3rem] text-lg cursor-pointer mt-4 shadow-neutral-600 bg-green-300 p-2 hover:bg-green-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
      @click="$emit('closeMenu')"
    >
      <i class="pi pi-book" />
      EXERCICES
    </router-link>
  </div>

  <div class="flex w-full md:justify-start justify-end items-center">
    <div class="max-xl:mt-2 mb-96  max-xl:p-3 gap-y-5 flex flex-col max-xl:w-full">
      <h3>documentations :</h3>
      <LogoList />
    </div>
  </div>
</template>

<script setup>
import SelectfunctionVocabulaire from './SelectfunctionVocabulaire.vue';
import LogoList from '../features/home/LogoList.vue';

defineEmits(['closeMenu']);
</script>
