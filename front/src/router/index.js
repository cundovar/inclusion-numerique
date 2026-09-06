import { createRouter, createWebHistory } from 'vue-router'
import { authGuard } from './guards'

// Views (lazy-loaded)
const PageContent = () => import('../components/features/pages/PageContent.vue')
const Profile = () => import('../views/Profile.vue')
const CategoryPage = () => import('../views/CategoryPage.vue')
const PageQCM = () => import('../views/QCM/PageQCM.vue')
const Exercices = () => import('../views/exercices.vue')
const ExerciceView = () => import('../components/features/exercices/ExerciceView.vue')
const questionQCM = () => import('../views/QCM/questionQCM.vue')
const resultQCM = () => import('../views/QCM/resultQCM.vue')
const ComponentsDemo = () => import('../views/ComponentsDemo.vue')
const HomePage = () => import('../components/features/home/HomePage.vue')

const routes = [
  // Route accueil
  {
    path: '/',
    name: 'home',
    component: HomePage
  },

  // Redirections des anciennes routes
  {
    path: '/pages/symfony',
    redirect: '/category/symfony'
  },
  {
    path: '/pages/vuejs',
    redirect: '/category/vuejs'
  },
  {
    path: '/pages/reactjs',
    redirect: '/category/react'
  },
  {
    path: '/pages/wp',
    redirect: '/category/wordpress'
  },

  // Route Profile (protegee)
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    beforeEnter: authGuard
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

  // Exercices
  {
    path: '/exercices',
    name: 'exercices',
    component: Exercices
  },
  {
    path: '/exercices/:slug',
    name: 'exercices-id',
    component: ExerciceView
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

export default router
