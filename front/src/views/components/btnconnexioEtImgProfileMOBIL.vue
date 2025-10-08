<template>
     <div class="   max-xl:w-5/6  flex max-xl:mt-10 items-center gap-2">
        <a
          v-if="user.roles.includes('ROLE_ADMIN')"
          target="_blank"
          :href="APP_CONFIG.ADMIN_URL"
          class="cursor-pointer text-amber-900 px-2 py-1 rounded text-sm"
        >
          <i class=" text-2xl pi pi-cog"></i>
        </a>
        <inputSearch
          v-if="user.roles.includes('ROLE_USER')"
        v-model="search"
        @search="launchSearch"
        placeholder="Rechercher"
        class="flex-1 xl:hidden   "
      />
    
        <button 
          @click="logout"
          :class="user.roles.includes('ROLE_USER') ? 'cursor-pointer border ml-2 text-red-600 hover:text-red-800 flex items-center gap-1 px-2 py-1 rounded hover:bg-red-100 transition-colors' : 'cursor-pointer border absolute right-2 ml-2 text-red-600 hover:text-red-800 flex items-center gap-1 px-2 py-1 rounded hover:bg-red-100 transition-colors'"
        >
          <i class="text-2xl pi pi-sign-out"></i>
        
        </button>
      </div>
</template>



<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { onMounted } from 'vue';
import inputSearch from './inputSearch.vue';
import { APP_CONFIG } from '../../config/app.js';

const user = ref({ username: '', roles: [] });

const search = defineModel('search', {
  type: String,
  default: ''
});

defineProps({
  launchSearch: {
    type: Function,
    default: () => {}
  }
});
const logout = async () => {
  try {
    await axios.post('/logout');
    window.location.href = '/login';
  } catch (error) {
    console.error('Erreur lors de la déconnexion', error);
  }
};

const fetchUser = async () => {
  try {
    const response = await axios.get('/user-api/me');
    user.value = response.data;
    console.log('User:', user.value);
  } catch (error) {
    console.error('Erreur lors de la récupération de l\'utilisateur', error);
  }
};

onMounted(() => {
  fetchUser();
 
});
</script>