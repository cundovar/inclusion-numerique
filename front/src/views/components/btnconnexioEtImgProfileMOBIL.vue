<template>
  <div class="flex items-center gap-2 w-full">
    <a
      v-if="user.roles.includes('ROLE_ADMIN')"
      target="_blank"
      :href="APP_CONFIG.ADMIN_URL"
      class="cursor-pointer text-amber-900 px-2 py-1 rounded text-sm"
    >
      <i class="text-2xl pi pi-cog"></i>
    </a>
    <inputSearch
      v-model="search"
      @search="handleSearch"
      placeholder="Rechercher"
      class="flex-1"
    />
  </div>
</template>



<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import inputSearch from './inputSearch.vue';
import { APP_CONFIG } from '../../config/app.js';

const user = ref({ username: '', roles: [] });

const search = defineModel('search', {
  type: String,
  default: ''
});

const props = defineProps({
  launchSearch: {
    type: Function,
    default: () => {}
  }
});

const handleSearch = () => {
  if (props.launchSearch) {
    props.launchSearch();
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