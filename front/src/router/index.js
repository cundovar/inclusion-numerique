import { createRouter, createWebHistory } from 'vue-router';

import PageComponent from '../components/PageComponent.vue';
import Profile from '../views/Profile.vue';
import CategoryPage from '../views/CategoryPage.vue';
import PageQCM from '../views/QCM/PageQCM.vue';
import Exercices from '../views/exercices.vue';
import ExoComponent from '../components/ExoComponent.vue';
import questionQCM from '../views/QCM/questionQCM.vue';
import resultQCM from '../views/QCM/resultQCM.vue';
import ComponentsDemo from '../views/ComponentsDemo.vue';

const routes = [
  // Redirection des anciennes routes vers CategoryPage
  {
    path: '/pages/symfony',
    redirect: '/category/symfony'
  },
  {
    path: '/pages/vuejs',
    redirect: '/category/vue'
  },
  {
    path: '/pages/reactjs',
    redirect: '/category/react'
  },
  {
    path: '/pages/wp',
    redirect: '/category/wordpress'
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile
  },
  {
    path: '/components-demo',
    name: 'components-demo',
    component: ComponentsDemo
  },
  {
    path: '/category/:category',
    name: 'category',
    component: CategoryPage,
    props: true
  },


  {
    path: '/pages/:slug',
    name: 'pages',
    component: PageComponent,
    props: true
  },
  {
    path: '/exercices',
    name: 'exercices',
    component: Exercices
  },
  {
    path: '/exercices/:slug',
    name: 'exercices-id',
    component: ExoComponent,
    
  },

  {
    path:'/qcm',
    name:'PageQCM',
    component:PageQCM
  },
  {
  path:'/qcm/:index',
  name:'questionQCM',
  component:questionQCM,
  props:true
},
{
  path:'/qcm/result',
  name:'resultQCM',
  component:resultQCM,
  props:true
},
];

const router = createRouter({
  history: createWebHistory('/spa'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Si on utilise les boutons précédent/suivant du navigateur
    if (savedPosition) {
      return savedPosition;
    }
    // Si on a un hash (#) dans l'URL (pour les ancres)
    else if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth',
      };
    }
    // Sinon, toujours scroller en haut
    else {
      return {
        top: 0,
        behavior: 'smooth'
      };
    }
  }
});
export default router;