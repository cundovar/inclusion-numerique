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
    </main>

    <navFooterMobil @click="closed" />
  </div>
</template>

<script setup>
import { computed, onMounted,ref } from "vue";
import pagesFrame from "./components/navigation/PagesFrame.vue";
import navFooterMobil from "./components/navigation/NavFooterMobile.vue";
import { useData } from "./utils/fetchDataPwa";
import { usePageTracking } from "./composables/domain/usePageTracking.js";
const pageTrackingEnabled = import.meta.env.VITE_ENABLE_PAGE_TRACKING === 'true';

// Composants importés
import AppHeader from "./components/layout/AppHeader.vue";
import AppHeaderMobile from "./components/layout/AppHeaderMobile.vue";
import AppNavigation from "./components/layout/AppNavigation.vue";
import MobileAppPopup from "./components/layout/MobileAppPopup.vue";
import NotifModal from "./components/layout/NotifModal.vue";
import SearchResults from "./components/features/search/SearchResults.vue";
import SearchResultsMobile from "./components/features/search/SearchResultsMobile.vue";

// Composables
import { useResponsive } from "./composables/ui/useResponsive.js";
import { useNavigation } from "./composables/ui/useNavigation.js";
import { useSearch } from "./composables/domain/useSearch.js";
import { usePWA } from "./composables/api/usePWA.js";
import { useNotifModal } from "./composables/ui/useNotifModal.js";
import { useVisibilityFilter } from "./composables/ui/useVisibilityFilter.js";
import { useCustomization } from "./composables/ui/useCustomization.js";
import AppButton from "./components/ui/AppButton.vue";

// =============================================================================
// STORE NIVEAU - Filtrage global par niveau de cours (Junior, Senior, etc.)
// =============================================================================
// Ce store permet de filtrer les catégories/cours dans toute l'application.
// Quand l'utilisateur sélectionne un niveau dans le header (AppSelect),
// toutes les pages qui utilisent ce store se mettent à jour automatiquement.
// Le niveau sélectionné est persisté dans localStorage (via pinia-plugin-persistedstate)
import { useNiveauStore } from "./stores/niveauCoursStore";
import { storeToRefs } from "pinia";

// Initialisation du store niveau
// storeToRefs() permet de garder la réactivité des propriétés du store
// selectedNiveau: le niveau actuellement sélectionné (ex: "Junior", "Senior", ou null pour tous)
const niveauStore = useNiveauStore()
const { selectedNiveau } = storeToRefs(niveauStore)

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
  openModal,
  closed
} = useNavigation();

const { search, searchResults, searchAnalysis, launchSearch, closeSearchResults } = useSearch(menus, user, isMobile, openModal);
const { showMobileAppPopup, currentOrigin, checkMobileAppPopup, closeMobileAppPopup, installPWA } = usePWA();
const { showNotifModal, closeNotifModal } = useNotifModal();
const { filterByVisibility } = useVisibilityFilter();
const { body, initCustomization } = useCustomization();

const sectionSize= ref(false)
const toggleSectionSize = () => {
  sectionSize.value = !sectionSize.value
}

// =============================================================================
// COMPUTED: catsMenuGauche - Catégories filtrées pour le menu de navigation
// =============================================================================
// Cette computed filtre les catégories en 3 étapes:
// 1. Position: seulement les catégories du menu-gauche ou menu-droite
// 2. Visibilité: filtre selon les droits utilisateur (admin, connecté, etc.)
// 3. NIVEAU: filtre par le niveau sélectionné dans le header (Junior/Senior/etc.)
const catsMenuGauche = computed(() => {
  // Étape 1: Filtrer par position dans le menu
  let menuGaucheCategories = (cats.value || []).filter(
    (cat) => cat.positionMenus?.position === "menu-gauche" ||
             cat.positionMenus?.position === "menu-droite"
  )

  // Étape 2: Appliquer le filtre de visibilité (droits utilisateur)
  menuGaucheCategories = filterByVisibility(menuGaucheCategories)

  // Étape 3: FILTRAGE PAR NIVEAU
  // Si un niveau est sélectionné (ex: "Junior"), on ne garde que les catégories
  // dont le niveauCours.name correspond au niveau sélectionné.
  // Si selectedNiveau est null, on affiche toutes les catégories (pas de filtre)
  if (selectedNiveau.value) {

    menuGaucheCategories = menuGaucheCategories.filter(
      (cat) => {
        const catOrdre = cat.niveauCours?.ordre
        const selectedOrdre = selectedNiveau.value.ordre
        const hasNoNiveau = !cat.niveauCours?.name

        // DEBUG: voir les valeurs
        console.log(`📦 ${cat.name}: catOrdre=${catOrdre}, selectedOrdre=${selectedOrdre}, hasNoNiveau=${hasNoNiveau}`)

        // Garder la catégorie si:
        // 1. Son niveau a un ordre <= à l'ordre du niveau sélectionné
        // 2. OU si la catégorie n'a pas de niveau défini (on l'affiche quand même)
        return catOrdre <= selectedOrdre || hasNoNiveau
      }
    )
  }

  console.log('🔍 catsMenuGauche - selectedNiveau:', selectedNiveau.value, '- count:', menuGaucheCategories.length)
  return menuGaucheCategories
});
// =============================================================================
// COMPUTED: menusByCategory - Menus groupés par catégorie
// =============================================================================
// Cette computed organise les menus par catégorie pour l'affichage dans la navigation.
// IMPORTANT: Elle utilise catsMenuGauche qui est DÉJÀ FILTRÉ par niveau.
// Donc quand selectedNiveau change, menusByCategory se recalcule automatiquement
// car catsMenuGauche (sa dépendance) a changé. C'est la magie de la réactivité Vue!
const menusByCategory = computed(() => {
  const result = {};

  // On itère sur les catégories DÉJÀ FILTRÉES par niveau
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

  // Charger les niveaux de cours depuis l'API pour le filtre du header
  // Les niveaux seront disponibles dans le store pour AppHeader et toutes les pages
  niveauStore.fetchNiveaux();

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
