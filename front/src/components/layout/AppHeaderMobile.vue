<template>
  <div class="border-blue-950 z-[999999999] py-2 bg-[var(--primary-color)] fixed w-full shadow-xl top-0 xl:hidden">
    <!-- Ligne du haut: Menu burger, Logo, Bouton auth -->
    <div class="flex justify-between items-center px-3 py-1">
      <!-- Bouton menu burger -->
      <div @click="$emit('toggleMenu')" class="cursor-pointer">
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
      <router-link to="/">
        <div class="flex items-center gap-2">
          <i class="pi pi-code text-blue-950" style="font-size: 1.8rem"></i>
          <span class="text-blue-950 font-bold text-xl">DevDoc</span>
        </div>
      </router-link>

      <!-- Bouton connexion/déconnexion -->
      <AuthButtonMobile />
    </div>

    <!-- Ligne du bas: Barre de recherche -->
    <div class="px-3 pt-1 pb-2">
      <btnconnexioEtImgProfileMOBIL
        :search="search"
        @update:search="$emit('update:search', $event)"
        :launchSearch="() => $emit('search')"
      />
    </div>
  </div>
</template>

<script setup>
import AuthButtonMobile from '../AuthButtonMobile.vue';
import btnconnexioEtImgProfileMOBIL from '../btnconnexioEtImgProfileMOBIL.vue';

defineProps({
  isMenuOpen: {
    type: Boolean,
    default: false
  },
  search: {
    type: String,
    default: ''
  }
});

defineEmits(['toggleMenu', 'update:search', 'search']);
</script>
