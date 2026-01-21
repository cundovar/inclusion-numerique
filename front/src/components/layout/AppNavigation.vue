<template>
  <div
    :class="[
      'xl:z-30 scroll-gauche   xl:top-20 min-h-screen fixed border-blue-950 overflow-y-scroll div-scrollbar transition-all duration-500 ease-in-out',
      isXlOuPlus ? '' : isMenuOpen ? 'max-xl:w-full md:w-2/3 z-40 top-28 ' : 'w-full -translate-x-full top-28',
      hoveredCategory ? 'z-40 w-1/2 ' : 'z-0 w-[20rem]',
      isOpenMenuGauche ? '-translate-x-[80%]' : 'translate-x-0',
    ]"
  >
    <nav
      @mouseleave="isXlOuPlus && $emit('hoverCategory', null)"
      :class="[
        'md:p-5  gap-y-2 relative 2xl:top-1 float-left z-50 h-screen max-md:z-50 transition-transform duration-300 ease-in-out',
        isXlOuPlus
          ? 'flex xl:w-[16rem] 2xl:w-[20rem] flex-col translate-x-0'
          : isMenuOpen
          ? 'w-full pl-2 pr-2 bg-gray-200 translate-x-0 max-h-[calc(100vh-112px)] max-md:h-[calc(100vh-112px)] max-md:overflow-y-auto flex flex-col max-md:z-50'
          : 'max-lg:w-full lg:w-1/2 -translate-x-full md:-translate-x-[60rem] lg:-translate-x-[70rem]',
      ]"
    >
      <div class="max-xl:hidden">
        <ButtonArrowMenu
          :fonction="() => $emit('toggleMenuGauche')"
          :isOpen="isOpenMenuGauche"
          ajoutClass="absolute right-3 top-0"
        />
      </div>

      <a
        class="md:hidden border max-xl:border-b-gray-50 bg-gray-500 gap-2 flex items-center justify-start p-1 hover:bg-gray-600"
        href="https://github.com/poleS-dev"
        target="_blank"
      >
        <i class="pi pi-github cursor-pointer text-amber-200 right-1/2 top-5" style="font-size:2rem"></i>
        <p class="text-amber-200 font-bold">GITHUB</p>
      </a>

      <!-- Menu des catégories -->
      <CategoryMenu
        :cats="cats"
        :menusByCategory="menusByCategory"
        :hoveredCategory="hoveredCategory"
        :clickMenu="clickMenu"
        :isXlOuPlus="isXlOuPlus"
        :islgOuPlus="islgOuPlus"
        :isOpenMenuGauche="isOpenMenuGauche"
        @openMenu="$emit('openMenu', $event)"
        @hoverCategory="$emit('hoverCategory', $event)"
        @closeMenu="$emit('closeMenu')"
      />
        <router-link
      to="/qcm"
      class=" max-xl:hidden xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="fas fa-brain"></i>
    QCM
    </router-link>
    
    <router-link
      to="/exercices"
      class=" max-xl:hidden xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer shadow-neutral-600 bg-green-300 p-2 hover:bg-green-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
    >
      <i class="pi pi-book"></i>
      EXERCICES
    </router-link>
    
    <SelectfunctionVocabulaire class="max-xl:hidden"/>

      <!-- Liens QCM, Exercices, Documentation -->
      <MenuLinks @closeMenu="$emit('closeMenu')" />
    </nav>
  </div>
</template>

<script setup>
import ButtonArrowMenu from '../ui/ButtonArrowMenu.vue';
import CategoryMenu from '../navigation/CategoryMenu.vue';
import MenuLinks from '../navigation/MenuLinks.vue';
import SelectfunctionVocabulaire from '../navigation/SelectfunctionVocabulaire.vue';
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
