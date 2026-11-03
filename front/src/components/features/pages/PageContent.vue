<template>
  <div
    class="flex pb-96 md:mt-16 xl:mt-0 p-2 md:p-10 lg:p-5 shadow-2xl bg-gray-50 md:rounded-2xl relative z-20 w-full overflow-x-hidden"
  >
    <div
      v-if="pageContent"
      class="page-layout w-full"
    >
      <!-- Retour à la page précédente -->
      <button
        type="button"
        class="back-link md:ml-10 lg:ml-20 px-3 md:px-5 pt-3"
        @click="goBack"
      >
        <i
          class="fas fa-arrow-left"
          aria-hidden="true"
        />
        Retour
      </button>

      <!-- Breadcrumb -->
      <SafeHtml
        :html="'accueil/' + (pageContent.menu?.label || pageContent.category?.name || 'Cours')"
        class="md:ml-10 lg:ml-20 p-3 md:p-5"
      />

      <!-- Contenu principal centre -->
      <div class="content-container flex items-start relative">
        <!-- Sommaire a gauche -->
        <SidebarToc
          :toc="toc"
          :active-id="activeId"
          :show-progress="true"
          :page-id="getPageId(pageContent.page)"
          :bg-categorie="pageContent.category?.couleur || ''"
          class="hidden xl:block"
          @scroll-to="scrollToHeading"
        />
        <main class="main-content flex-1">
          <div class="course-header">
            <h1 class="course-title">
              {{ pageContent.title }}
            </h1>
            <NiveauBadge
              :niveau="pageContent.niveauCours"
              :niveaux="niveaux"
              class="course-level"
            />
          </div>

          <!-- Un brouillon n'est servi par l'API qu'aux administrateurs, pour relecture -->
          <p
            v-if="pageContent.status && pageContent.status !== 'publie'"
            class="draft-banner"
            role="status"
          >
            Ce cours n'est pas publié : seuls les administrateurs le voient.
          </p>

          <!-- Lu sans réseau : copie gardée sur l'appareil au dernier lancement en ligne -->
          <p
            v-if="horsLigne"
            class="offline-banner"
            role="status"
            data-testid="cours-hors-ligne"
          >
            Vous êtes hors ligne : version du cours enregistrée sur cet appareil lors de votre dernière connexion.
          </p>

          <!-- Cours et captures en PDF, pour l'imprimer ou le garder -->
          <!-- Outils du cours : PDF à garder ou imprimer, lecture à voix haute (synthèse vocale du navigateur) -->
          <div class="course-tools">
            <PdfDownload :href="`/cours/${slug}.pdf`" />
            <ReadAloud
              v-if="renderedCode"
              :key="slug"
              :target="contentRef"
            />
          </div>

          <!-- Contenu de la page -->
          <div
            v-if="renderedCode"
            ref="contentRef"
            class=" "
          >
            <SafeHtml
              :html="renderedCode"
              class="course-body max-md:w-full xl:w-full m-auto p-5"
            />
          </div>

          <!-- Navigation Precedent / Suivant -->
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
                <span class="md:hidden">Precedent</span>
              </AppButton>
            </router-link>
            <div v-else />

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
            <div v-else />
          </div>
        </main>
      </div>
    </div>

    <div
      v-else
      class="flex justify-center items-center h-full"
    >
      <i
        class="pi pi-spin m-5 p-5 rounded-full shadow-2xl pi-cog"
        style="font-size: 3rem"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import Prism from 'prismjs'
import api from '@/services/api'
import SidebarToc from './toc/SidebarToc.vue'
import ReadAloud from './ReadAloud.vue'
import AppButton from '../../ui/AppButton.vue'
import SafeHtml from '../../ui/SafeHtml.vue'
import NiveauBadge from '../../ui/NiveauBadge.vue'
import PdfDownload from './PdfDownload.vue'
import { expandCaptures } from '@/utils/captures'
import { structureCourse } from '@/utils/courseSections'
import { compareCourses } from '@/utils/niveaux'
import { storeToRefs } from 'pinia'
import { useNiveauStore } from '@/stores/niveauCoursStore'
import { useToc } from '@/composables/ui/useToc'
import { useVisibilityFilter } from '@/composables/ui/useVisibilityFilter'
import { chargerCaptures } from '@/services/captures'
import { useData } from '@/utils/fetchDataPwa'

// Props
const props = defineProps({
  slug: {
    type: String,
    required: true
  }
})

// Composables
const router = useRouter()
const contentRef = ref(null)
const { toc, activeId, scrollToHeading, generateToc } = useToc(contentRef, {
  includeH3: false,
  offsetTop: 120
})
const { filterByVisibility } = useVisibilityFilter()
const { findCourseOffline, listCoursesOffline } = useData()
const niveauStore = useNiveauStore()
const { niveaux } = storeToRefs(niveauStore)

// State
const pageContent = ref(null)
// Contenu prêt à afficher : marqueurs [capture:ID] remplacés par les captures, parties du gabarit repérées
const renderedCode = ref('')
const previousPage = ref(null)
const nextPage = ref(null)
const allPages = ref([])
const horsLigne = ref(false)

// Methods
// Revient à la page précédente du site ; arrivé directement sur le cours (lien, favori), on va à son parcours
function goBack() {
  if (window.history.state?.back) {
    router.back()
    return
  }
  const parcours = pageContent.value?.category?.name
  router.push(parcours ? `/category/${parcours.toLowerCase()}` : '/')
}

function getPageId(page) {
  // Si c'est un objet avec un ID classique
  if (typeof page === 'object' && page?.id) {
    return page.id
  }

  // Si c'est un objet avec @id (ApiPlatform)
  if (typeof page === 'object' && page?.['@id']) {
    const iri = page['@id']
    if (iri.includes('/api/pages/')) {
      return parseInt(iri.split('/api/pages/')[1])
    }
  }

  // Si c'est une URL IRI d'ApiPlatform (ex: "/api/pages/1")
  if (typeof page === 'string' && page.includes('/api/pages/')) {
    return parseInt(page.split('/api/pages/')[1])
  }

  // Si c'est juste un ID
  if (typeof page === 'number') {
    return page
  }

  return null
}

// Cours demandé à l'API ; sans réseau, copie enregistrée par fetchMenus (voir utils/fetchDataPwa.js)
async function loadCourse() {
  try {
    const { data } = await api.get(
      `/page_contents?page.slug=/${encodeURIComponent(props.slug)}`
    )
    return { course: data.totalItems > 0 ? data.member[0] : null, copie: false }
  } catch (err) {
    if (err.response) throw err
    return { course: await findCourseOffline(props.slug), copie: true }
  }
}

async function fetchPageContent() {
  renderedCode.value = ''
  try {
    const { course, copie } = await loadCourse()
    // Copie de l'appareil, ou réponse gardée par le service worker en mode avion
    horsLigne.value = copie || (typeof navigator !== 'undefined' && navigator.onLine === false)

    if (course) {
      pageContent.value = course
      renderedCode.value = structureCourse(await expandCaptures(pageContent.value.code, chargerCaptures))

      nextTick(() => {
        document.querySelectorAll('pre code').forEach((el) => {
          el.className = el.className.trim()

          if (!el.className.includes('language-')) {
            el.classList.add('language-js')
          }

          el.innerHTML = el.innerHTML.replace(/^\s+/gm, '')
        })

        Prism.highlightAll()

        setTimeout(() => {
          generateToc()
        }, 200)

        fetchNavigationPages()
      })
    }
  } catch (err) {
    console.error('Erreur API:', err)
  }
}

async function fetchNavigationPages() {
  try {
    const menuId = pageContent.value?.menu?.id || pageContent.value?.menu?.['@id']

    if (!menuId) {
      return
    }

    let menuApiId = menuId
    if (typeof menuId === 'string' && menuId.includes('/api/menus/')) {
      menuApiId = menuId.split('/api/menus/')[1]
    }

    let pages
    try {
      const { data } = await api.get(`/page_contents?menu.id=${menuApiId}&order[position]=asc`)
      pages = data.member
    } catch (err) {
      if (err.response) throw err
      // Hors ligne : liste de tous les cours enregistrée par fetchMenus, réduite à cette rubrique
      pages = (await listCoursesOffline())
        .filter((p) => String(p.menu?.id ?? p.menu?.['@id']?.split('/api/menus/')[1]) === String(menuApiId))
    }

    if (pages.length > 0) {
      // Même ordre que la page du parcours : du niveau le plus simple au plus avancé
      allPages.value = [...filterByVisibility(pages)].sort(compareCourses)

      const currentIndex = allPages.value.findIndex(
        p => p.page?.slug === `/${props.slug}` || p.page?.slug === props.slug
      )

      // Page precedente
      if (currentIndex > 0) {
        previousPage.value = {
          title: allPages.value[currentIndex - 1].title,
          slug: allPages.value[currentIndex - 1].page?.slug || ''
        }
      } else {
        previousPage.value = null
      }

      // Page suivante
      if (currentIndex >= 0 && currentIndex < allPages.value.length - 1) {
        nextPage.value = {
          title: allPages.value[currentIndex + 1].title,
          slug: allPages.value[currentIndex + 1].page?.slug || ''
        }
      } else {
        nextPage.value = null
      }
    }
  } catch (err) {
    console.error('Erreur lors de la recuperation de la navigation:', err)
  }
}

// Watchers
watch(
  () => props.slug,
  (newSlug, oldSlug) => {
    if (newSlug !== oldSlug) {
      window.scrollTo({ top: 0, behavior: 'smooth' })
      fetchPageContent()
    }
  }
)

// Lifecycle
onMounted(() => {
  fetchPageContent()
  niveauStore.fetchNiveaux()
})
</script>

<style scoped>
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e3a8a;
  text-decoration: underline;
  background: none;
  border: none;
  cursor: pointer;
}

.course-level {
  margin-top: 0.75rem;
}

.course-tools {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 0.75rem;
  max-width: 44rem;
  margin: 1rem auto 0;
  padding: 0 1.25rem;
}

/* Texte du cours : voir assets/styles/content-cours.css */
.course-header {
  max-width: 44rem;
  margin: 0 auto;
  padding: 1.25rem 1.25rem 0;
}

.course-title {
  font-family: 'Atkinson Hyperlegible', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  font-size: 2.1rem;
  line-height: 1.25;
  font-weight: 700;
  color: #111827 !important; /* main.css grise tous les h1 */
  text-align: left;
}

@media (max-width: 640px) {
  .course-header {
    padding: 0.75rem 1.25rem 0;
  }

  .course-title {
    font-size: 1.7rem;
  }
}

.draft-banner {
  margin: 0 1.25rem 1rem;
  padding: 0.75rem 1rem;
  border-left: 6px solid #b45309;
  background: #fffbeb;
  color: #78350f;
  font-weight: 600;
}

.offline-banner {
  margin: 0 1.25rem 1rem;
  padding: 0.75rem 1rem;
  border-left: 6px solid #2563eb;
  background: #eff6ff;
  color: #1e3a8a;
  font-weight: 600;
}

/* Layout: Contenu centre */
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

/* Empecher le debordement du contenu HTML */
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
</style>
