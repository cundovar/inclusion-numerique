<template>
  <!-- <pagesFrame/> -->

  <!-- Pop-up de téléchargement d'app mobile -->
  <MobileAppPopup
    v-if="showMobileAppPopup"
    :current-origin="currentOrigin"
    :instructions="instructions"
    @close="closeMobileAppPopup"
    @install="installPWA"
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
      :is-menu-open="isMenuOpen"

      @toggle-menu="toggleMenu"
    />

    <!-- Résultats de recherche desktop -->
    <SearchResults
      :search-results="searchResults"
      :search-analysis="searchAnalysis"
      :search="search"
      @close="closeSearchResults"
    />



    <div class="h-28" />

    <!-- Navigation principale -->
    <AppNavigation
      :cats="catsMenuGauche"
      :menus-by-category="menusByCategory"
      :hovered-category="hoveredCategory"
      :click-menu="clickMenu"
      :is-menu-open="isMenuOpen"
      :is-xl-ou-plus="isXlOuPlus"
      :islg-ou-plus="islgOuPlus"
      :is-open-menu-gauche="isOpenMenuGauche"
      @open-menu="openMenu"
      @hover-category="(cat) => hoveredCategory = cat"
      @close-menu="closeMobileMenu"
      @toggle-menu-gauche="toggleMenuGauche"
    />

    <!-- Contenu principal -->
    <main class="relative xl:p-20 max-md:z-10 max-md:w-full h-auto xl:w-4/5 xl:ml-16 xl:self-center right-0 overflow-hidden">
      <router-view />
    </main>

    <!-- Liens légaux en bas de chaque page -->
    <AppFooter />

    <navFooterMobil @click="closed" />

    <!-- Information sur les cookies, affichée une fois -->
    <CookieNotice />
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import navFooterMobil from "./components/navigation/NavFooterMobile.vue";
import { useData } from "./utils/fetchDataPwa";

// Composants importés
import AppHeader from "./components/layout/AppHeader.vue";
import AppHeaderMobile from "./components/layout/AppHeaderMobile.vue";
import AppNavigation from "./components/layout/AppNavigation.vue";
import MobileAppPopup from "./components/layout/MobileAppPopup.vue";
import CookieNotice from "./components/layout/CookieNotice.vue";
import AppFooter from "./components/layout/AppFooter.vue";
import SearchResults from "./components/features/search/SearchResults.vue";

// Composables
import { useResponsive } from "./composables/ui/useResponsive.js";
import { useNavigation } from "./composables/ui/useNavigation.js";
import { useSearch } from "./composables/domain/useSearch.js";
import { usePWA } from "./composables/api/usePWA.js";
import { useVisibilityFilter } from "./composables/ui/useVisibilityFilter.js";
import { useCustomization } from "./composables/ui/useCustomization.js";

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

// Composables
const { isMobile, islgOuPlus, isXlOuPlus, updateScreenSizes } = useResponsive();
const {
  hoveredCategory,
  isMenuOpen,
  clickMenu,
  isOpenMenuGauche,
  toggleMenu,
  closeMobileMenu,
  openMenu,
  toggleMenuGauche,
  openModal,
  closed
} = useNavigation();

const { search, searchResults, searchAnalysis, launchSearch, closeSearchResults } = useSearch(menus, user, isMobile, openModal);
const { showMobileAppPopup, currentOrigin, instructions, checkMobileAppPopup, closeMobileAppPopup, installPWA } = usePWA();
const { filterByVisibility } = useVisibilityFilter();
const { body, initCustomization } = useCustomization();

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
      //   console.log(`📦 ${cat.name}: catOrdre=${catOrdre}, selectedOrdre=${selectedOrdre}, hasNoNiveau=${hasNoNiveau}`)

        // Garder la catégorie si:
        // 1. Son niveau a un ordre <= à l'ordre du niveau sélectionné
        // 2. OU si la catégorie n'a pas de niveau défini (on l'affiche quand même)
        return catOrdre <= selectedOrdre || hasNoNiveau
      }
    )
  }

 // console.log('🔍 catsMenuGauche - selectedNiveau:', selectedNiveau.value, '- count:', menuGaucheCategories.length)
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


    let menusForCat = (menus.value || []).filter(
      (menu) => {
       //  console.log("DEBUG menu.category?.name:", menu.category?.name, "vs cat.name:", cat.name);
        return menu.category?.name === cat.name;

      }
    );

    if(selectedNiveau.value){
      console.log("🔍 selectedNiveau:", selectedNiveau.value, "ordre:", selectedNiveau.value.ordre)
      menusForCat = menusForCat.filter((menu) => {
        const selectedOrdre = selectedNiveau.value.ordre

        // Niveau du PageContent direct
        const pageContentOrdre = menu.niveauCours?.ordre
        // Niveau du Menu associé
        const menuNiveauOrdre = menu.menu?.niveauCours?.ordre

        // Utiliser le niveau du PageContent en priorité, sinon celui du Menu
        const effectiveOrdre = pageContentOrdre ?? menuNiveauOrdre

        console.log(`📦 ${menu.title}: pageContentOrdre=${pageContentOrdre}, menuNiveauOrdre=${menuNiveauOrdre}, effectiveOrdre=${effectiveOrdre}, selectedOrdre=${selectedOrdre}, pass=${effectiveOrdre === undefined || effectiveOrdre <= selectedOrdre}`)

        // Si aucun niveau défini nulle part, on garde l'élément
        if (effectiveOrdre === undefined) return true

        return effectiveOrdre <= selectedOrdre
      })
    }

    // console.log("dddsds",menusForCat)//


    menusForCat.forEach((menu) => {
      const label = menu.menu?.label?.trim() || 'Cours';


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

  //console.log("menusByCategory result:", result);

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
