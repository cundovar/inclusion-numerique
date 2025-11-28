<template>
  <div class="text-2xl max-md:text-xl xl:hidden max-md:drop-shadow-xl pb-2 mt-4 pl-1">
    <SelectfunctionVocabulaire @closeMenu="$emit('closeMenu')" />

    <router-link
      to="/qcm"
      @click="$emit('closeMenu')"
      class="text-xl h-[3rem] cursor-pointer mt-4 shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="fas fa-brain"></i>
      QCM
    </router-link>

    <router-link
      to="/exercices"
      @click="$emit('closeMenu')"
      class="text-xl h-[3rem] cursor-pointer mt-4 shadow-neutral-600 bg-green-300 p-2 hover:bg-green-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="pi pi-book"></i>
      EXERCICES
    </router-link>
  </div>

  <div class="flex w-full md:justify-start justify-end items-center">
    <div class="max-xl:mt-2 mb-96 max-xl:bg-amber-300 max-xl:p-3 gap-y-5 flex flex-col max-xl:w-full">
      <h3>documentations :</h3>
      <list_logo />
    </div>
  </div>
</template>

<script setup>
import SelectfunctionVocabulaire from '../MenuPageFramwork/SelectfunctionVocabulaire.vue';
import list_logo from '../list_logo.vue';

defineEmits(['closeMenu']);
</script>
