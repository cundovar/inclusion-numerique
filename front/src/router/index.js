import { createRouter, createWebHistory } from 'vue-router'
import { installerRechargementVersion } from './rechargementVersion'

// Views (lazy-loaded)
const PageContent = () => import('../components/features/pages/PageContent.vue')
const CategoryPage = () => import('../views/CategoryPage.vue')
const PageQCM = () => import('../views/QCM/PageQCM.vue')
const questionQCM = () => import('../views/QCM/questionQCM.vue')
const resultQCM = () => import('../views/QCM/resultQCM.vue')
const ComponentsDemo = () => import('../views/ComponentsDemo.vue')
const HomePage = () => import('../components/features/home/HomePage.vue')
const AudiencePage = () => import('../views/AudiencePage.vue')
const ExercicesPage = () => import('../views/ExercicesPage.vue')
const ExercicePage = () => import('../views/ExercicePage.vue')
const MonEspacePage = () => import('../views/MonEspacePage.vue')

const routes = [
  // Route accueil
  {
    path: '/',
    name: 'home',
    component: HomePage
  },

  // Les deux entrées de l'accueil : catalogue des parcours de chaque public
  {
    path: '/apprendre',
    name: 'apprendre',
    component: AudiencePage,
    props: { audienceKey: 'apprendre' }
  },
  {
    path: '/accompagner',
    name: 'accompagner',
    component: AudiencePage,
    props: { audienceKey: 'accompagner' }
  },

  // Exercices interactifs (données dans src/exercices, rien n'est enregistré)
  {
    path: '/exercices',
    name: 'exercices',
    component: ExercicesPage
  },
  {
    path: '/exercices/:slug',
    name: 'exercice',
    component: ExercicePage,
    props: true
  },

  // Espace de l'apprenant connecté : cours, parcours et exercices personnalisés, compte
  {
    path: '/mon-espace',
    name: 'mon-espace',
    component: MonEspacePage
  },

  // Demo composants
  {
    path: '/components-demo',
    name: 'components-demo',
    component: ComponentsDemo
  },

  // Categories
  {
    path: '/category/:category',
    name: 'category',
    component: CategoryPage,
    props: true
  },

  // Pages de contenu
  {
    path: '/pages/:slug',
    name: 'pages',
    component: PageContent,
    props: true
  },

  // QCM
  {
    path: '/qcm',
    name: 'PageQCM',
    component: PageQCM
  },
  {
    path: '/qcm/:index',
    name: 'questionQCM',
    component: questionQCM,
    props: true
  },
  {
    path: '/qcm/result',
    name: 'resultQCM',
    component: resultQCM,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory('/spa'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      }
    }
    return {
      top: 0,
      behavior: 'smooth'
    }
  }
})

installerRechargementVersion(router)

export default router
