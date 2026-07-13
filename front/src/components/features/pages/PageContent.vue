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

      <!-- Contenu principal centre -->
      <div class="content-container flex items-start relative">
              <!-- Sommaire a gauche -->
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
              class="max-md:absolute top-10 right-1"
            />
          </div>

          <!-- Contenu de la page -->
          <div class=" " v-if="pageContent.code" ref="contentRef">

<SafeHtml
              :html="pageContent.code"
              class="text-center max-md:w-full xl:w-full m-auto flex p-5"

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

<script setup>
import { ref, watch, onMounted, onUpdated, nextTick } from 'vue'
import Prism from 'prismjs'
import api from '@/services/api'
import FavoriteButton from '../favorites/FavoriteButton.vue'
import SidebarToc from './toc/SidebarToc.vue'
import AppButton from '../../ui/AppButton.vue'
import SafeHtml from '../../ui/SafeHtml.vue'
import { useToc } from '@/composables/ui/useToc'
import { useVisibilityFilter } from '@/composables/ui/useVisibilityFilter'

const pageTrackingEnabled = import.meta.env.VITE_ENABLE_PAGE_TRACKING === 'true'

// Props
const props = defineProps({
  slug: {
    type: String,
    required: true
  }
})

// Composables
const contentRef = ref(null)
const { toc, activeId, scrollToHeading, generateToc } = useToc(contentRef, {
  includeH3: false,
  offsetTop: 120
})
const { filterByVisibility } = useVisibilityFilter()

// State
const pageContent = ref(null)
const previousPage = ref(null)
const nextPage = ref(null)
const allPages = ref([])

// Methods
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

async function fetchPageContent() {
  try {
    const { data } = await api.get(
      `/page_contents?page.slug=/${encodeURIComponent(props.slug)}`
    )

    if (data.totalItems > 0) {
      pageContent.value = data.member[0]

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

    const { data } = await api.get(`/page_contents?menu.id=${menuApiId}&order[position]=asc`)

    if (data.totalItems > 0) {
      allPages.value = filterByVisibility(data.member)

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

async function trackPageVisit() {
  if (!pageTrackingEnabled || !pageContent.value?.page) {
    return
  }

  try {
    const trackData = {
      pageUrl: pageContent.value.page.slug || `/${props.slug}`,
      pageTitle: pageContent.value.title || 'Page sans titre'
    }

    await api.post('/page-visits/track', trackData)
  } catch {
    // Ignorer silencieusement les erreurs
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
})

onUpdated(() => {
  if (pageTrackingEnabled && pageContent.value?.page) {
    trackPageVisit()
  }
})
</script>

<style scoped>
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

/* Amelioration de la lisibilite du contenu */
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
