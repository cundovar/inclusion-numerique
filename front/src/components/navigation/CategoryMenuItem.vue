<template>
  <div
    @click="!isXlOuPlus && $emit('openMenu', cat.name)"
    class="cursor-pointer flex relative flex-col justify-center"
    @mouseenter="isXlOuPlus && $emit('hoverCategory', cat.name)"
  >
    <!-- Titre de la catégorie -->
    <h1
      :class="[{
        'bg-yellow-200': (islgOuPlus && hoveredCategory === cat.name) || (!islgOuPlus && clickMenu === cat.name),
      }, isOpenMenuGauche ? 'flex items-end justify-end ' : '']"
      class="text-2xl max-md:text-xl max-md:drop-shadow-xl max-md:drop-shadow-gray-400 max-md:border-b max-md:border-gray-400 pt-2 hover:bg-blue-400 md:mt-2 max-md:text-center uppercase max-md:bg-blue-200 bg-blue-300 pb-2 pl-1"
    >
      <!-- Desktop : lien direct vers la catégorie -->
      <router-link
        v-if="isXlOuPlus"
        :to="`/category/${cat.name.toLowerCase()}`"
        @click="$emit('closeMenu')"
        class="cursor-pointer block focus:bg-blue-500"
      >
        {{ cat.name }}
      </router-link>

      <!-- Mobile/Tablet : texte cliquable qui ouvre le menu -->
      <span
        v-else
        class="cursor-pointer max-md:w-full max-md:h-full block max-md:text-center"
      >
        {{ cat.name }}
      </span>
    </h1>

    <!-- Sous-menu déroulant -->
    <div class="xl:absolute xl:top-0 xl:-right-[26rem] xl:z-[99999] xl:shadow-lg max-h-[20rem] xl:pr-10 xl:pl-10 xl:w-[25rem] rounded-xl bg-gray-200 div-scrollbar overflow-y-auto">
      <!-- Bouton "Voir tous" en mobile/tablet -->
      <div v-if="!isXlOuPlus && clickMenu === cat.name" class="p-2">
        <router-link
          :to="`/category/${cat.name.toLowerCase()}`"
          @click="$emit('closeMenu')"
          class="block w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md text-center transition-all duration-200"
        >
          <i class="pi pi-eye mr-2"></i>
          Voir tous les cours de {{ cat.name }}
        </router-link>
      </div>

      <!-- Menu des cours -->
      <div
        class="md:gap-y-1"
        v-if="(isXlOuPlus && hoveredCategory === cat.name) || (!isXlOuPlus && clickMenu === cat.name)"
        v-for="group in menusByCategory[cat.name]"
        :key="group.label"
      >
        <div class="p-2 md:w-2/3 md:mt-1 md:border-b border-green-950 mb-2 md:text-start md:bg-transparent md:text-black max-md:border-b max-md:hover:bg-indigo-200 max-md:border-b-blue-300 max-md:border-t-blue-300 max-md:bg-indigo-100 max-md:text-xl max-md:border-t">
          {{ group.label }}
        </div>

        <button
          class="w-full md:rounded-sm shadow-2xl border border-indigo-800 mb-2 xl:ml-2 flex hover:bg-gray-300 bg-gray-100 flex-col gap-2"
          v-for="menu in group.items"
          :key="menu.page.slug"
        >
          <router-link
            :to="`/pages${menu.page.slug}`"
            @click="$emit('closeMenu')"
            class="focus:bg-gray-400 focus:text-white z-50 hover:text-blue-600"
          >
            <p class="p-2 md:text-blue-500 hover:text-fuchsia-900 flex justify-around max-md:justify-start items-center max-md:text-start">
              <div class="flex justify-center items-center w-1/3">
                <i class="pi pi-code max-md:text-blue-600 md:text-yellow-600"></i>
              </div>

              <div class="flex-1 text-center border-blue-300 shadow border flex justify-center items-center">
                {{ capitalize(menu.title) }}
              </div>

              <div class="flex justify-center items-center w-1/3">
                <i class="pi pi-code max-md:text-blue-600 md:text-yellow-600"></i>
              </div>
            </p>
          </router-link>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { capitalize } from '../../utlis/stringsUtlis';

defineProps({
  cat: {
    type: Object,
    required: true
  },
  menusByCategory: {
    type: Object,
    required: true
  },
  hoveredCategory: {
    type: String,
    default: null
  },
  clickMenu: {
    type: String,
    default: null
  },
  isXlOuPlus: {
    type: Boolean,
    default: false
  },
  islgOuPlus: {
    type: Boolean,
    default: false
  },
  isOpenMenuGauche: {
    type: Boolean,
    default: false
  }
});

defineEmits(['openMenu', 'hoverCategory', 'closeMenu']);
</script>
