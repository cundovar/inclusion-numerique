<template>
  <div class="hidden xl:flex xl:w-full xl:fixed xl:top-0 xl:right-0 xl:z-50 xl:justify-between xl:items-center xl:px-8 backdrop-blur-md bg-white/30 py-4">
    <!-- Logo DevDoc -->
    <router-link to="/">
      <div class="flex gap-5 items-center rounded-lg px-4 py-2">
        <i class="pi pi-code text-blue-950" style="font-size: 1.8rem"></i>
        <span class="text-gray-800 font-bold text-xl">DevDoc</span>
        <i class="pi pi-code text-blue-950" style="font-size: 1.8rem"></i>
      </div>
    </router-link>

    <!-- Recherche au centre -->
    <inputSearch
      :modelValue="search"
      @update:modelValue="$emit('update:search', $event)"
      @search="$emit('search')"
      placeholder="Rechercher"
      class="w-96"
    />

    <!-- Boutons profil et déconnexion à droite -->
    <btnconnexioEtImgProfile />
  </div>
</template>

<script setup>
import inputSearch from '../inputSearch.vue';
import btnconnexioEtImgProfile from '../btnconnexioEtImgProfile.vue';

defineProps({
  search: {
    type: String,
    default: ''
  }
});

defineEmits(['update:search', 'search']);
</script>
