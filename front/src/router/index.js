import { createRouter, createWebHistory } from 'vue-router';
import HomeComponent from '../views/components/HomeComponent.vue';
import PageComponent from '../views/components/PageComponent.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeComponent
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