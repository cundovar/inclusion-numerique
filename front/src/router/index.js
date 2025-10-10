import { createRouter, createWebHistory } from 'vue-router';

import PageComponent from '../views/components/PageComponent.vue';
import Symfony from '../views/pageDesCours/Symfony.vue';
import Vuejs from '../views/pageDesCours/Vuejs.vue';
import Reactjs from '../views/pageDesCours/Reactjs.vue';
import Wordpress from '../views/pageDesCours/Wordpress.vue';
import Profile from '../views/Profile.vue';
import CategoryPage from '../views/CategoryPage.vue';
import PageQCM from '../views/QCM/PageQCM.vue';
import Exercices from '../views/exercices.vue';
import ExoComponent from '../views/components/ExoComponent.vue';

const routes = [
  {
    path: '/pages/symfony',
    name: 'symfony',
    component: Symfony
  },
  {
    path: '/pages/vuejs',
    name: 'vuejs',
    component: Vuejs
  },
  {
    path: '/pages/reactjs',
    name: 'reactjs',
    component: Reactjs
  },
  {
    path: '/pages/wp',
    name: 'wordpress',
    component: Wordpress
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile
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
  }
];

const router = createRouter({
  history: createWebHistory('/spa'),
  routes
});
export default router;