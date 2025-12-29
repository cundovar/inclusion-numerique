<template>
  <div
    class="flex pb-96 md:mt-16 xl:mt-0 p-2 md:p-10 lg:p-5 shadow-2xl bg-gray-50 md:rounded-2xl relative z-20 w-full overflow-x-hidden"
  >

    <div v-if="pageContent" class="page-layout w-full">
     

      <!-- Breadcrumb -->
      <SafeHtml
        :html="'accueil/' + pageContent.menu.label"
        class="md:ml-10 lg:ml-20 p-3 md:p-5"
      ></SafeHtml>

      <!-- Contenu principal centré -->
      <div class="content-container flex items-start relative">
              <!-- Sommaire à gauche -->
              <SidebarToc
                :toc="toc"
                :activeId="activeId"
                :showProgress="true"
                :pageId="getPageId(pageContent.page)"
                :bgCategorie="pageContent.category?.couleur || ''"
                @scroll-to="scrollToHeading"
                class="hidden xl:block"
              />
              <main class="main-content flex-1">
       
          <div class="flex justify-between items-center p-5 max-md:p-2">
            <h1 class="text-2xl text-center flex-1">{{ pageContent.title }}</h1>
            <FavoriteButton
              v-if="pageContent.page && getPageId(pageContent.page)"
              :pageId="getPageId(pageContent.page)"
              class="max-md:absolute top-24 right-3"
            />
          </div>

          <!-- Contenu de la page -->
          <div class=" " v-if="pageContent.code" ref="contentRef">

<SafeHtml
              :html="pageContent.code"
              class="text-center max-md:w-full xl:w-full m-auto flex p-5"
             
            />

          </div>

          <!-- Navigation Précédent / Suivant -->
          <div class="flex justify-center items-center p-3 md:p-5 mt-10 border-t border-gray-300 gap-4 md:gap-10">

            <router-link
              v-if="previousPage"
              :to="`/pages/${previousPage.slug.replace('/', '')}`"
            >
              <AppButton
                variant="ghost"
                icon="pi pi-arrow-left"
                size="md"
              >
                <span class="max-md:hidden">{{ previousPage.title }}</span>
                <span class="md:hidden">Précédent</span>
              </AppButton>
            </router-link>
            <div v-else></div>

            <router-link
              v-if="nextPage"
              :to="`/pages/${nextPage.slug.replace('/', '')}`"
            >
              <AppButton
                variant="ghost"
                icon="pi pi-arrow-right"
                icon-position="right"
                size="md"
              >
                <span class="max-md:hidden">{{ nextPage.title }}</span>
                <span class="md:hidden">Suivant</span>
              </AppButton>
            </router-link>
            <div v-else></div>

          </div>

        </main>
      </div>
    </div>

    <div class="flex justify-center items-center h-full" v-else>
      <i
        class="pi pi-spin m-5 p-5 rounded-full shadow-2xl pi-cog"
        style="font-size: 3rem"
      ></i>
    </div>

  </div>
</template>

<script>
import Prism from "prismjs";
import axios from "axios";
import { ref } from 'vue';
import FavoriteButton from "./FavoriteButton.vue";
import TableOfContents from "./TableOfContents.vue";
import SidebarToc from "./SidebarToc.vue";
import AppButton from "./commun/button/AppButton.vue";
import SafeHtml from "./security-html-bdd/safeHtml.vue";
import { useToc } from "@/composables/useToc";
import { useVisibilityFilter } from "@/composables/useVisibilityFilter";
import TableContent2 from "./TableContent2.vue";
const pageTrackingEnabled = import.meta.env.VITE_ENABLE_PAGE_TRACKING === 'true';

export default {
  components: {
    FavoriteButton,
    TableOfContents,
    SidebarToc,
    AppButton,
    SafeHtml,
  },
  props: ["slug"],
  setup() {
    const contentRef = ref(null);
    const { toc, activeId, scrollToHeading, generateToc } = useToc(contentRef, {
      includeH3: false,
      offsetTop: 120
    });
    const { filterByVisibility } = useVisibilityFilter();

    return {
      contentRef,
      toc,
      activeId,
      scrollToHeading,
      generateToc,
      filterByVisibility,
    };
  },
  data() {
    return {
      pageContent: null,
      previousPage: null,
      nextPage: null,
      allPages: []
    };
  },
  watch: {
    slug(newslug, oldslug) {
      if (newslug !== oldslug) {
        // Scroller en haut de page
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
        this.fetchPageContent();
      }
    },
  },
  mounted() {
    this.fetchPageContent();
  },
  updated() {
    // Enregistrer la visite après le chargement du contenu
    if (pageTrackingEnabled && this.pageContent && this.pageContent.page) {
      this.trackPageVisit();
    }
  },
  methods: {
    async fetchPageContent() {
      try {
        const res = await axios.get(
          `/api/page_contents?page.slug=/${encodeURIComponent(this.slug)}`
        );
        if (res.data.totalItems > 0) {
          this.pageContent = res.data.member[0];
          console.log("pageContent complet:", this.pageContent);
          console.log("pageContent.page:", this.pageContent.page);
          console.log("page ID:", this.pageContent.page?.id);
           console.log("pageContent.category:",this.pageContent.category.couleur)

          this.$nextTick(() => {
            document.querySelectorAll("pre code").forEach((el) => {
              // Nettoie la classe : enlève espaces
              el.className = el.className.trim();

              // Si pas de language-xxx, mets un langage par défaut (ex : js)
              if (!el.className.includes("language-")) {
                el.classList.add("language-js");
              }

              // Supprime indentation inutile dans les lignes
              el.innerHTML = el.innerHTML.replace(/^\s+/gm, "");
            });

            // Maintenant on applique Prism
            Prism.highlightAll();

            // Générer le sommaire après le rendu du contenu
            setTimeout(() => {
              this.generateToc();
            }, 200);

            // Charger la navigation (précédent/suivant)
            this.fetchNavigationPages();
          });
        }
      } catch (err) {
        console.error("Erreur API:", err);
      }
    },


    async fetchNavigationPages() {
      try {
        // Récupérer le menu ID de la page actuelle
        const menuId = this.pageContent?.menu?.id || this.pageContent?.menu?.['@id'];

        if (!menuId) {
          console.log("Pas de menu trouvé pour cette page");
          return;
        }

        // Construire l'URL pour récupérer toutes les pages du même menu
        let menuApiId = menuId;
        if (typeof menuId === 'string' && menuId.includes('/api/menus/')) {
          menuApiId = menuId.split('/api/menus/')[1];
        }

        const res = await axios.get(`/api/page_contents?menu.id=${menuApiId}&order[position]=asc`);

        if (res.data.totalItems > 0) {
          // Filtrer uniquement les pages visibles
          this.allPages = this.filterByVisibility(res.data.member);

          // Trouver l'index de la page actuelle
          const currentIndex = this.allPages.findIndex(
            p => p.page?.slug === `/${this.slug}` || p.page?.slug === this.slug
          );

          console.log("Pages du menu:", this.allPages.length, "Index actuel:", currentIndex);

          // Définir la page précédente
          if (currentIndex > 0) {
            this.previousPage = {
              title: this.allPages[currentIndex - 1].title,
              slug: this.allPages[currentIndex - 1].page?.slug || ''
            };
          } else {
            this.previousPage = null;
          }

          // Définir la page suivante
          if (currentIndex >= 0 && currentIndex < this.allPages.length - 1) {
            this.nextPage = {
              title: this.allPages[currentIndex + 1].title,
              slug: this.allPages[currentIndex + 1].page?.slug || ''
            };
          } else {
            this.nextPage = null;
          }

          console.log("Previous:", this.previousPage?.title, "Next:", this.nextPage?.title);
        }
      } catch (err) {
        console.error("Erreur lors de la récupération de la navigation:", err);
      }
    },

    getPageId(page) {
      console.log("getPageId called with:", page, "type:", typeof page);

      // Si c'est un objet avec un ID classique
      if (typeof page === "object" && page.id) {
        console.log("Found object with id:", page.id);
        return page.id;
      }

      // Si c'est un objet avec @id (ApiPlatform)
      if (typeof page === "object" && page["@id"]) {
        const iri = page["@id"];
        if (iri.includes("/api/pages/")) {
          const id = iri.split("/api/pages/")[1];
          const parsedId = parseInt(id);
          console.log("Extracted ID from @id:", parsedId);
          return parsedId;
        }
      }

      // Si c'est une URL IRI d'ApiPlatform (ex: "/api/pages/1")
      if (typeof page === "string" && page.includes("/api/pages/")) {
        const id = page.split("/api/pages/")[1];
        const parsedId = parseInt(id);
        console.log("Extracted ID from IRI string:", parsedId);
        return parsedId;
      }

      // Si c'est juste un ID
      if (typeof page === "number") {
        console.log("Direct number ID:", page);
        return page;
      }

      console.log("No valid ID found");
      return null;
    },

    async trackPageVisit() {
      if (!pageTrackingEnabled) {
        return;
      }

      try {
        if (!this.pageContent || !this.pageContent.page) {
          console.log("Cannot track visit: no page content");
          return;
        }

        const trackData = {
          pageUrl: this.pageContent.page.slug || `/${this.slug}`,
          pageTitle: this.pageContent.title || 'Page sans titre'
          
        };

        console.log('Tracking page visit:', trackData);

        await axios.post('/api/page-visits/track', trackData);
      } catch (error) {
        // Ignorer silencieusement les erreurs (ex: utilisateur non connecté)
        console.log('Page visit tracking skipped:', error.response?.status);
      }
    },
  },
};


   
</script>

<style scoped>
/* Layout: Contenu centré */
.content-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  width: 100%;
  box-sizing: border-box;
}

.main-content {
  min-width: 0;
  width: 100%;
  overflow-x: hidden;
}

/* Responsive Mobile */
@media (max-width: 768px) {
  .content-container {
    max-width: 100%;
    padding: 0 0.5rem;
  }
}

@media (max-width: 1024px) {
  .content-container {
    max-width: 100%;
  }
}

/* Empêcher le débordement du contenu HTML */
.main-content :deep(pre),
.main-content :deep(code),
.main-content :deep(table) {
  max-width: 100%;
  overflow-x: auto;
}

.main-content :deep(img) {
  max-width: 100%;
  height: auto;
}

/* Amélioration de la lisibilité du contenu */
.main-content >>> h2 {
  margin-top: 2rem;
  margin-bottom: 1rem;
  padding-top: 1rem;
  scroll-margin-top: 120px; /* Offset pour le header fixe */
}

.main-content >>> h3 {
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  scroll-margin-top: 120px;
}

/* Style pour les ancres de titres (optionnel) */
.main-content >>> h2:hover::before,
.main-content >>> h3:hover::before {
  content: '#';
  position: absolute;
  left: -1.5rem;
  color: #42b983;
  opacity: 0.7;
  font-weight: 400;
}
</style>
