<template>
  <!-- <pagesFrame/> -->

  <!-- Pop-up de téléchargement d'app mobile -->
  <MobileAppPopup
    v-if="showMobileAppPopup"
    :currentOrigin="currentOrigin"
    @close="closeMobileAppPopup"
    @install="installPWA"
  />

  <!-- Pop-up de notification -->
  <NotifModal
    v-if="showNotifModal"
    @close="closeNotifModal"
  />

  <div
    id="app2"
    :class="[
      body.bgColor,
      body.textColor,
      'relative w-full xl:w-full m-auto'
    ]"
  >
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
    <main :class="sectionSize ? 'w-full min-h-screen z-[60] mt-0 pt-0 absolute ' : ' relative xl:p-20 max-md:z-10 max-md:w-full h-auto xl:w-4/5 xl:ml-16 xl:self-center right-0 overflow-hidden'">
      <div v-if="!$route.path.includes('/exercices') && !$route.path.includes('/pages') && !$route.path.includes('/profile') && !$route.path.includes('/qcm') && !$route.path.includes('/category') && $route.path !== '/'" class="max-xl:hidden absolute top-15 z-70 right-50" >

        <AppButton v-if="sectionSize " variant="danger" text-content="fermer" type="button" @click="toggleSectionSize" />
        <AppButton v-else variant="danger" text-content="agrandir" type="button" @click="toggleSectionSize" />

      </div>
      <router-view />
      <AfertLogin v-if="$route.path === '/'" />
    </main>

    <navFooterMobil @click="closed" />
  </div>
</template>

<script setup>
import { computed, onMounted,ref } from "vue";
import pagesFrame from "./components/MenuPageFramwork/PagesFrame.vue";
import AfertLogin from "./components/AfertLogin.vue";
import navFooterMobil from "./components/NavFooterMobil/Main.vue";
import { useData } from "./utlis/fetchDataPwa";
import { usePageTracking } from "./composables/usePageTracking.js";
const pageTrackingEnabled = import.meta.env.VITE_ENABLE_PAGE_TRACKING === 'true';

// Composants importés
import AppHeader from "./components/layout/AppHeader.vue";
import AppHeaderMobile from "./components/layout/AppHeaderMobile.vue";
import AppNavigation from "./components/layout/AppNavigation.vue";
import MobileAppPopup from "./components/layout/MobileAppPopup.vue";
import NotifModal from "./components/layout/NotifModal.vue";
import SearchResults from "./components/search/SearchResults.vue";
import SearchResultsMobile from "./components/search/SearchResultsMobile.vue";

// Composables
import { useResponsive } from "./composables/useResponsive.js";
import { useNavigation } from "./composables/useNavigation.js";
import { useSearch } from "./composables/useSearch.js";
import { usePWA } from "./composables/usePWA.js";
import { useNotifModal } from "./composables/useNotifModal.js";
import { useVisibilityFilter } from "./composables/useVisibilityFilter.js";
import { useCustomization } from "./composables/useCustomization.js";
import AppButton from "./components/commun/button/AppButton.vue";

// Données globales
const { menus, user, cats, fetchMenus, fetchUser } = useData();

// Initialiser le tracking des visites de pages
if (pageTrackingEnabled) {
  usePageTracking();
}

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
const { showNotifModal, closeNotifModal } = useNotifModal();
const { filterByVisibility } = useVisibilityFilter();
const { body, initCustomization } = useCustomization();

const sectionSize= ref(false)
const toggleSectionSize = () => {
  sectionSize.value = !sectionSize.value
}
// Filtrer les catégories visibles du menu gauche
const catsMenuGauche = computed(() => {
  const menuGaucheCategories = (cats.value || []).filter(
    (cat) => cat.positionMenus?.position === "menu-gauche" || cat.positionMenus?.position === "menu-droite"
  );

  // Appliquer le filtre de visibilité
  return filterByVisibility(menuGaucheCategories);
});
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

  // Initialiser la personnalisation utilisateur
  initCustomization();

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
