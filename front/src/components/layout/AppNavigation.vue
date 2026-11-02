<template>
  <!--
    Colonne fixe : exactement la hauteur visible sous l'en-tête, avec une barre de défilement seulement si le menu
    est plus long. Sans débordement, la molette fait défiler la page même quand la souris est sur la colonne.
    La colonne laisse passer la souris (pointer-events-none) : seul le menu lui-même capte clics et molette.
  -->
  <div
    :class="[
      'xl:z-30 scroll-gauche xl:top-20 h-[calc(100vh-7rem)] xl:h-[calc(100vh-5rem)] fixed border-blue-950 overflow-y-auto pointer-events-none div-scrollbar transition-all duration-500 ease-in-out',
      isXlOuPlus ? '' : isMenuOpen ? 'max-xl:w-full md:w-2/3 z-40 top-28 ' : 'w-full -translate-x-full top-28',
      hoveredCategory ? 'z-40 w-1/2 ' : 'z-0 w-[20rem]',
      isOpenMenuGauche ? '-translate-x-[80%]' : 'translate-x-0',
    ]"
  >
    <nav
      :class="[
        'md:p-5 gap-y-2 relative float-left z-50 min-h-full pointer-events-auto max-md:z-50 transition-transform duration-300 ease-in-out',
        isXlOuPlus
          ? 'flex xl:w-[16rem] 2xl:w-[20rem] flex-col translate-x-0'
          : isMenuOpen
            ? 'w-full pl-2 pr-2 bg-gray-200 translate-x-0 max-h-[calc(100vh-112px)] max-md:h-[calc(100vh-112px)] max-md:overflow-y-auto flex flex-col max-md:z-50'
            : 'max-lg:w-full lg:w-1/2 -translate-x-full md:-translate-x-[60rem] lg:-translate-x-[70rem]',
      ]"
      @mouseleave="isXlOuPlus && $emit('hoverCategory', null)"
    >
      <div class="max-xl:hidden">
        <ButtonArrowMenu
          :fonction="() => $emit('toggleMenuGauche')"
          :is-open="isOpenMenuGauche"
          ajout-class="absolute right-3 top-0"
        />
      </div>

      <!-- Menu des catégories -->
      <CategoryMenu
        :cats="cats"
        :menus-by-category="menusByCategory"
        :hovered-category="hoveredCategory"
        :click-menu="clickMenu"
        :is-xl-ou-plus="isXlOuPlus"
        :islg-ou-plus="islgOuPlus"
        :is-open-menu-gauche="isOpenMenuGauche"
        @open-menu="$emit('openMenu', $event)"
        @hover-category="$emit('hoverCategory', $event)"
        @close-menu="$emit('closeMenu')"
      />
      <!-- Les deux entrées de l'accueil -->
      <router-link
        v-for="audience in AUDIENCES"
        :key="audience.key"
        :to="audience.path"
        :class="[
          audience.key === 'apprendre' ? 'bg-blue-200 hover:bg-blue-300' : 'bg-emerald-200 hover:bg-emerald-300',
          'max-xl:hidden xl:w-full min-h-[3rem] cursor-pointer shadow-neutral-600 p-2 text-gray-900 font-bold hover:underline flex items-center gap-2'
        ]"
      >
        <i
          :class="audience.icon"
          aria-hidden="true"
        />
        {{ audience.title }}
      </router-link>
      <router-link
        to="/exercices"
        class="max-xl:hidden xl:w-full min-h-[3rem] cursor-pointer shadow-neutral-600 bg-amber-200 p-2 hover:bg-amber-300 text-gray-900 font-bold hover:underline flex items-center gap-2"
      >
        <i
          class="fas fa-puzzle-piece"
          aria-hidden="true"
        />
        Exercices
      </router-link>
      <router-link
        to="/qcm"
        class=" max-xl:hidden xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
      >
        <i class="fas fa-brain" />
        QCM
      </router-link>



      <!-- Liens QCM -->
      <MenuLinks @close-menu="$emit('closeMenu')" />
    </nav>
  </div>
</template>

<script setup>
import ButtonArrowMenu from '../ui/ButtonArrowMenu.vue';
import CategoryMenu from '../navigation/CategoryMenu.vue';
import MenuLinks from '../navigation/MenuLinks.vue';
import { AUDIENCES } from '@/config/audiences';
defineProps({
  cats: {
    type: Array,
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
  isMenuOpen: {
    type: Boolean,
    default: false
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

defineEmits(['openMenu', 'hoverCategory', 'closeMenu', 'toggleMenuGauche']);
</script>

<style scoped>
.div-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #778899 transparent;
}

.div-scrollbar::-webkit-scrollbar {
  width: 4px;
  border-radius: 4px;
}

.div-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.div-scrollbar::-webkit-scrollbar-thumb {
  background-color: #87cefa;
  border-radius: 10px;
  box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.2);
}

.div-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #555;
}
</style>
