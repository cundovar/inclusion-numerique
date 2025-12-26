<template>
  <div  
    @click="!isXlOuPlus && $emit('openMenu', cat.name)"
    class="cursor-pointer flex relative  px-2 flex-col justify-center"
    @mouseenter="isXlOuPlus && $emit('hoverCategory', cat.name)"
  >
    <!-- Titre de la catégorie -->
    <h1
      :class="[
        menuGauche.categoryBgColor,
        menuGauche.categoryTextColor,
        menuGauche.categoryTextSize,
        menuGauche.categoryHoverColor,
        {
          'bg-yellow-200': (islgOuPlus && hoveredCategory === cat.name) || (!islgOuPlus && clickMenu === cat.name),
        },
        isOpenMenuGauche ? 'flex items-end justify-end ' : ''
      ]"
      class=" max-md:drop-shadow-xl max-md:drop-shadow-gray-400 max-md:border-b max-md:border-gray-400 pt-2 md:mt-2 max-md:text-center uppercase  pb-2 pl-1"
    >
      <!-- Desktop : lien direct vers la catégorie -->
      <router-link
        v-if="isXlOuPlus"
        :to="`/category/${cat.name.toLowerCase()}`"
        @click="$emit('closeMenu')"
        class="cursor-pointer text-lg hover:underline block  focus:bg-blue-500"
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
    <div class="xl:absolute xl:top-0 xl:-right-[26rem] xl:z-40 xl:shadow-lg max-h-[20rem] xl:pr-10 xl:pl-10 xl:w-[25rem] rounded-xl bg-gray-200 div-scrollbar overflow-y-auto">
      <!-- Bouton "Voir tous" en mobile/tablet -->
      <div v-if="!isXlOuPlus && clickMenu === cat.name" class="p-2">
        <router-link
          :to="`/category/${cat.name.toLowerCase()}`"
          @click="$emit('closeMenu')"
          class="block w-full"
        >
          <AppButton
            variant="ghost"
            size="md"
            icon="pi pi-eye"
            :text-content="`Voir tous les cours de ${cat.name}`"
            full-width
          />
        </router-link>
      </div>

      <!-- Menu des cours -->

      <div
        class="md:gap-y-1"
        v-if="(isXlOuPlus && hoveredCategory === cat.name) || (!isXlOuPlus && clickMenu === cat.name)"
        v-for="group in menusByCategory[cat.name]"
        :key="group.label"
      >
      
        <div class="p-2 md:w-2/3 md:mt-1 md:border-b mb-2 md:text-start md:bg-transparent   max-md:text-center  max-md:text-sm max-md:border-t">
          
          <p class="font-semibold  ">{{ group.label }}</p>
        </div>

        <button
          :class="[
            menuGauche.menuItemBgColor,
            menuGauche.menuItemHoverBgColor,
            'w-full md:rounded-sm shadow-2xl border border-indigo-800 mb-2 xl:ml-2 flex flex-col gap-2 text-sm'
          ]"
          v-for="menu in group.items"
          :key="menu.page.slug"
        >
          <router-link
            :to="`/pages${menu.page.slug}`"
            @click="$emit('closeMenu')"
            :class="[
              menuGauche.menuItemTextColor,
              'focus:bg-gray-400 focus:text-white z-50 hover:text-fuchsia-900'
            ]"
          >
            <div class="p-2 flex justify-around max-md:justify-start items-center max-md:text-start">
              <div class="flex justify-center items-center w-1/3">
                <i class="pi pi-code max-md:text-blue-600 md:text-yellow-600"></i>
              </div>

              <div class="flex-1 text-center border-blue-300 shadow border flex justify-center items-center">
                {{ menu.title }}
              </div>

              <div class="flex justify-center items-center w-1/3">
                <i class="pi pi-code max-md:text-blue-600 md:text-yellow-600"></i>
              </div>
            </div>
          </router-link>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { capitalize } from '../../utlis/stringsUtlis';
import { useCustomization } from '../../composables/useCustomization';
import AppButton from '../commun/button/AppButton.vue';

const { menuGauche } = useCustomization();

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
