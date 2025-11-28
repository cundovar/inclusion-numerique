<template>
  <pagesFrame/>

  <!-- Pop-up de téléchargement d'app mobile -->
  <MobileAppPopup
    v-if="showMobileAppPopup"
    :currentOrigin="currentOrigin"
    @close="closeMobileAppPopup"
    @install="installPWA"
  />

  <div id="app2" class="relative w-full xl:w-full m-auto">
    <!-- Header desktop -->
    <AppHeader
      v-model:search="search"
      @search="launchSearch"
    />

    <!-- Header mobile -->
    <AppHeaderMobile
      :isMenuOpen="isMenuOpen"
      v-model:search="search"
      @toggleMenu="toggleMenu"
      @search="launchSearch"
    />

    <!-- Résultats de recherche desktop -->
    <SearchResults
      :searchResults="searchResults"
      :searchAnalysis="searchAnalysis"
      :search="search"
      @close="closeSearchResults"
    />

    <!-- Résultats de recherche mobile -->
    <SearchResultsMobile
      :modalIsOpen="modalIsOpen"
      :searchResults="searchResults"
      :searchAnalysis="searchAnalysis"
      :search="search"
      @close="closeModal"
    />

    <div class="h-28"></div>

    <!-- Navigation principale -->
    <AppNavigation
      :cats="catsMenuGauche"
      :menusByCategory="menusByCategory"
      :hoveredCategory="hoveredCategory"
      :clickMenu="clickMenu"
      :isMenuOpen="isMenuOpen"
      :isXlOuPlus="isXlOuPlus"
      :islgOuPlus="islgOuPlus"
      :isOpenMenuGauche="isOpenMenuGauche"
      @openMenu="openMenu"
      @hoverCategory="(cat) => hoveredCategory = cat"
      @closeMenu="closeMobileMenu"
      @toggleMenuGauche="toggleMenuGauche"
    />

    <!-- Contenu principal -->
    <main class="xl:p-20 max-md:z-10 max-md:w-full h-auto xl:w-4/5 xl:ml-16 xl:self-center right-0 overflow-hidden">
      <router-view />
      <AfertLogin v-if="$route.path === '/'" />
    </main>

    <navFooterMobil @click="closed" />
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import pagesFrame from "./components/MenuPageFramwork/PagesFrame.vue";
import AfertLogin from "./components/AfertLogin.vue";
import navFooterMobil from "./components/NavFooterMobil/Main.vue";
import { useData } from "./utlis/fetchDataPwa";
import { usePageTracking } from "./composables/usePageTracking.js";

// Composants importés
import AppHeader from "./components/layout/AppHeader.vue";
import AppHeaderMobile from "./components/layout/AppHeaderMobile.vue";
import AppNavigation from "./components/layout/AppNavigation.vue";
import MobileAppPopup from "./components/layout/MobileAppPopup.vue";
import SearchResults from "./components/search/SearchResults.vue";
import SearchResultsMobile from "./components/search/SearchResultsMobile.vue";

// Composables
import { useResponsive } from "./composables/useResponsive.js";
import { useNavigation } from "./composables/useNavigation.js";
import { useSearch } from "./composables/useSearch.js";
import { usePWA } from "./composables/usePWA.js";

// Données globales
const { menus, user, cats, fetchMenus, fetchUser } = useData();

// Initialiser le tracking des visites de pages
usePageTracking();

// Composables
const { isMobile, isMdOuPlus, islgOuPlus, isXlOuPlus, updateScreenSizes } = useResponsive();
const {
  hoveredCategory,
  isMenuOpen,
  clickMenu,
  isOpenMenuGauche,
  modalIsOpen,
  toggleMenu,
  closeMobileMenu,
  openMenu,
  toggleMenuGauche,
  closeModal,
  closed
} = useNavigation();

const { search, searchResults, searchAnalysis, launchSearch, closeSearchResults } = useSearch(menus, user, isMobile);
const { showMobileAppPopup, currentOrigin, checkMobileAppPopup, closeMobileAppPopup, installPWA } = usePWA();
const catsMenuGauche = computed(() =>
  (cats.value || []).filter(
    (cat) => cat.positionMenus?.position === "menu-gauche"
  )
);
// Computed pour les menus par catégorie
// filtrage des categorie, je veux juste les categorie qui seront positionner en menu-gauche
const menusByCategory = computed(() => {
  const result = {};

  console.log("cats:", cats.value);
  console.log("menus:", menus.value);

  catsMenuGauche.value.forEach((cat) => {
    const grouped = {};

    const menusForCat = (menus.value || []).filter(
      (menu) => {
        return menu.category?.name === cat.name;
      }
    );
    console.log("menusForCat", menusForCat);

    menusForCat.forEach((menu) => {
      const label = menu.menu.label;

      if (!grouped[label]) {
        grouped[label] = {
          label,
          items: [],
        };
      }

      grouped[label].items.push(menu);
    });

    result[cat.name] = Object.values(grouped);
  });

  console.log("menusByCategory result:", result);

  return result;
});

onMounted(() => {
  fetchMenus();
  fetchUser();
  updateScreenSizes();

  // Vérifier si le pop-up mobile app doit être affiché avec un délai
  setTimeout(() => {
    checkMobileAppPopup(isMobile.value);
  }, 500);

  // Fermer le menu mobile quand on passe en desktop
  if (isXlOuPlus.value && isMenuOpen.value) {
    isMenuOpen.value = false;
  }
});
</script>

<style scoped>
.index-99 {
  z-index: 99999999999;
}
</style>
