import { createRouter, createWebHistory } from 'vue-router';

import PageComponent from '../views/components/PageComponent.vue';
import Symfony from '../views/Symfony.vue';
import Vuejs from '../views/Vuejs.vue';
import Reactjs from '../views/Reactjs.vue';
import ReactCourse from '../views/ReactCourse.vue';
import VueCourse from '../views/VueCourse.vue';
import Wordpress from '../views/Wordpress.vue';
import WordPressCourse from '../views/WordPressCourse.vue';
import Profile from '../views/Profile.vue';

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
    path: '/pages/react/:id',
    name: 'react-course',
    component: ReactCourse,
    props: true
  },
  {
    path: '/pages/vue/:id',
    name: 'vue-course',
    component: VueCourse,
    props: true
  },
  {
    path: '/pages/WP',
    name: 'wordpress',
    component: Wordpress 
  },
  {
    path: '/pages/WP/:id',
    name: 'wordpress-course',
    component: WordPressCourse,
    props: true
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile
  },
  {
    path: '/pages/:slug',
    name: 'pages',
    component: PageComponent,
    props: true
  }
];

const router = createRouter({
  history: createWebHistory('/spa'),
  routes
});
export default router;