<template>
  <!-- Composant SEO pour gérer les balises meta (seulement après le chargement des données) -->
  <SeoHead
    v-if="cats.length > 0"
    :seo-data="seoData"
  />

  <div
    class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full"
    :class="categoryBgClass"
  >
    <div class="">
      <div class="header-section">
        <router-link
          v-if="audience"
          :to="audience.path"
          class="back-link"
        >
          <i
            class="fas fa-arrow-left"
            aria-hidden="true"
          />
          {{ audience.title }}
        </router-link>
        <div class="tech-header">
          <i :class="categoryIconClass + ' tech-icon'" />
          <h1 class="tech-title">
            {{ categoryDisplayName }}
          </h1>
        </div>

        <div />


        <SafeHtml
          :html="categoryPresentation"
          class="framework-presentation"
        />

        <!-- Premier cours du niveau le plus simple, pour qui ne sait pas par où commencer -->
        <router-link
          v-if="startCourse"
          :to="`/pages${startCourse.page.slug}`"
          class="start-link"
        >
          <i
            class="fas fa-flag"
            aria-hidden="true"
          />
          Par où commencer ? {{ startCourse.title }}
        </router-link>

        <!-- Sens des marches affichées à côté de chaque cours -->
        <NiveauLegend
          v-if="startCourse"
          :niveaux="niveaux"
        />
      </div>

      <div class="flex flex-wrap justify-center items-start">
        <CourseCard
          v-for="group in categoryMenus"
          :key="group.label"
          :title="group.label"
          :menus="group.items"
          :niveaux="niveaux"
          :module-id="group.items[0]?.menu?.id ?? null"
          :icon-class="getGroupIconClass(group.label)"
        />
      </div>

      <div
        v-if="!categoryMenus.length"
        class="text-center py-12"
      >
        <p class="text-gray-500 text-lg">
          Ce parcours ne contient pas encore de cours.
        </p>
        <router-link
          to="/"
          class="mt-4 inline-block text-blue-500 hover:underline"
        >
          Retour à l'accueil
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useData } from '../utils/fetchDataPwa'
import CourseCard from '../components/features/pages/CourseCard.vue'
import SeoHead from '../components/layout/SeoHead.vue'
import SafeHtml from '../components/ui/SafeHtml.vue'
import NiveauLegend from '../components/ui/NiveauLegend.vue'
import { storeToRefs } from 'pinia'
import { useNiveauStore } from "@/stores/niveauCoursStore";
import { getAudience } from '@/config/audiences'
import { compareCourses, firstCourseToStart } from '@/utils/niveaux'



const route = useRoute()
const { fetchMenus, menus, cats } = useData()

//const pour les bouton selected niveaux
const niveauStore = useNiveauStore()
const { selectedNiveau, niveaux } = storeToRefs(niveauStore)
   console.log("localstorage",selectedNiveau.value)
const categoryName = computed(() => route.params.category)

// Entrée de l'accueil à laquelle appartient ce parcours, pour le lien de retour
const audience = computed(() => {
  const currentCategory = cats.value?.find(
    cat => cat.name?.toLowerCase() === categoryName.value.toLowerCase()
  )
  return getAudience(currentCategory?.audience)
})

// Récupérer les données SEO directement depuis la catégorie
const seoData = computed(() => {
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Si la catégorie a un SEO défini, l'utiliser
  if (currentCategory?.seo) {
    return currentCategory.seo
  }

  // Sinon, générer un SEO par défaut depuis les données de la catégorie
  if (currentCategory) {
    return {
      title: `${categoryDisplayName.value} - Parcours`,
      metaDescription: currentCategory.description ?
        currentCategory.description.replace(/<[^>]*>/g, '').substring(0, 160) :
        `Les cours du parcours ${categoryDisplayName.value}`,
      ogTitle: `${categoryDisplayName.value} - Parcours`,
      ogDescription: currentCategory.description ?
        currentCategory.description.replace(/<[^>]*>/g, '').substring(0, 200) :
        `Les cours du parcours ${categoryDisplayName.value}`
    }
  }

  return null
})

const categoryDisplayName = computed(() => {
  const name = categoryName.value
  return name.charAt(0).toUpperCase() + name.slice(1)
})

const categoryBgClass = computed(() => {
  // Chercher la catégorie dans l'API par son nom
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Retourner la couleur de l'API ou une couleur par défaut
  return currentCategory?.couleur || 'bg-gray-200'
})

const categoryIconClass = computed(() => {
  // D'abord, chercher l'icône dans l'API (category.logo.logo)
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Si l'icône existe dans l'API, l'utiliser
  if (currentCategory?.logo?.logo) {
    return currentCategory.logo.logo
  }

  return 'fas fa-book-open'
})

const categoryPresentation = computed(() => {
  // Chercher la catégorie dans l'API par son nom
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Retourner la description HTML de l'API
  if (currentCategory?.description) {
    return currentCategory.description

  }

  return '<div>Les cours de ce parcours, à suivre dans l\'ordre ou selon vos besoins.</div>'
})

const categoryMenus = computed(() => {
  if (!menus.value.length) return []

  console.log('🔍 Debug CategoryPage:')
  console.log('  - categoryName from URL:', categoryName.value)
  console.log('  - Total menus:', menus.value.length)

  // Afficher toutes les catégories disponibles
  const uniqueCategories = [...new Set(menus.value.map(m => m.category?.name).filter(Boolean))]
  console.log('  - Available categories in API:', uniqueCategories)

  const grouped = {}

  // Étape 1: Filtrer par catégorie
  let filteredMenus = menus.value.filter((menu) => {
    if (!menu.category) return false
    const matches = menu.category.name.toLowerCase() === categoryName.value.toLowerCase()

    // if (matches) {
    //   console.log('  ✅ Match found:', menu.title, '- Category:', menu)

    // }
    return matches
  })

  // console.log('  - Filtered menus count:', filteredMenus.length)
 // Étape 2: Filtrer par niveau (comme dans App.vue)
if(selectedNiveau.value){

  console.log("eee",filteredMenus)
  // Filtrage 1: Filtrer par le niveau du menu (menu.niveauCours)
  filteredMenus = filteredMenus.filter((item) => {
    const menuCatOrdre = item.menu?.niveauCours?.ordre
    const selectedOrdre = selectedNiveau.value.ordre
    const hasNoNiveau = !item.menu?.niveauCours?.name
    return menuCatOrdre <= selectedOrdre || hasNoNiveau
  })

  // Filtrage 2: Filtrer par le niveau du PageContent (niveauCours direct)
  filteredMenus = filteredMenus.filter((menu) => {
    const menuOrdre = menu.niveauCours?.ordre
    const selectedOrdre = selectedNiveau.value.ordre
    const hasNoNiveau = !menu.niveauCours?.name
    //garder menu si
    //1 ordre <=ordre du niv selectionne dans localstorage
    //2 ou si pas niveau defini
    return menuOrdre <=selectedOrdre || hasNoNiveau
  })
}
  // Étape 3: Regrouper par label
  filteredMenus.forEach((menu) => {
    const label = menu.menu?.label?.trim() || 'Cours'

    if (!grouped[label]) {
      grouped[label] = {
        label: label,
        slug: label.toLowerCase().replace(/\s+/g, '-'),
        items: [],
      }
    }

    grouped[label].items.push(menu)
  })

  // Dans chaque module, du niveau le plus simple au plus avancé, puis dans l'ordre pédagogique (rang)
  Object.values(grouped).forEach((group) => group.items.sort(compareCourses))

  return Object.values(grouped)
})

const startCourse = computed(() => firstCourseToStart(categoryMenus.value))

// Icône commune à tous les modules d'un parcours
const getGroupIconClass = () => 'fas fa-book'

onMounted(() => {
  fetchMenus()
  niveauStore.fetchNiveaux()
})
</script>

<style scoped>
.start-link {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  margin-top: 1.5rem;
  padding: 0.75rem 1.25rem;
  border-radius: 12px;
  background: #dcfce7;
  border: 1px solid #86efac;
  color: #14532d;
  font-size: 1.125rem;
  font-weight: 600;
  text-decoration: none;
}

.start-link:hover { background: #bbf7d0; }
.start-link:focus-visible { outline: 3px solid #1d4ed8; outline-offset: 2px; }

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e3a8a;
  text-decoration: underline;
}

.symfony-container {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 20px;
  padding-bottom: 120px;
}

.header-section {
  text-align: center;
  padding: 40px 0;
  margin-bottom: 40px;
}

.tech-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
  margin-bottom: 16px;
}

.tech-icon {
  font-size: 60px;
  color: #ffffff;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

.tech-title {
  font-size: 48px;
  font-weight: 500;
  color: #d4c1c1;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  margin: 0;
}

.tech-description {
  font-size: 18px;
  color: rgba(102, 102, 102, 0.9);
  margin: 0;
  font-weight: 500;
}

.framework-presentation {
  max-width: 800px;
  margin: 30px auto 0;
  padding: 25px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.framework-presentation p {
  font-size: 16px;
  line-height: 1.7;
  color: #444;
  margin: 0;
  text-align: justify;
  font-weight: 400;
}

.cards-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

@media screen and (max-width: 768px) {
  .tech-header {
    flex-direction: column;
    gap: 10px;
  }

  .tech-icon {
    font-size: 40px;
  }

  .tech-title {
    font-size: 32px;
  }

  .tech-description {
    font-size: 16px;
  }

  .framework-presentation {
    margin: 20px 10px 0;
    padding: 20px;
  }

  .framework-presentation p {
    font-size: 14px;
    line-height: 1.6;
    text-align: left;
  }

  .symfony-container {
    padding: 16px;
    padding-bottom: 120px;
  }
}
</style>
