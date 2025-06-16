import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import CourVue from '../views/CourVue.vue';
const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  {
    path: '/test',
    name: 'test',
    component: CourVue
    
    
   
    
  },
  {
    path: '/data',
    name: 'DataBing',
    component: {
      template: DataBing
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
