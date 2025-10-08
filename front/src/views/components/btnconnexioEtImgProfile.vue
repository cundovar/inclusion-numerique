<template>
     <div  class="flex items-center gap-2">
      <!-- Bouton profil -->
      <router-link to="/profile">
        <button v-if="user.roles.includes('ROLE_USER')" class="p-2 bg-amber-200 hover:bg-amber-300 text-gray-800 cursor-pointer flex items-center gap-2 rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
          <i class="pi pi-user"></i>
          <span class="font-medium">{{ user.username }}</span>
        </button>
      </router-link>
    <button 
  
        @click="logout"
        class="p-2 bg-pink-400 hover:bg-pink-500 text-white cursor-pointer flex items-center gap-2 rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl"
      >
        <i v-if="user.roles.includes('ROLE_USER')" class="pi pi-sign-out"></i>
        <i v-else class="pi pi-sign-in"></i>
        <span v-if="user.roles.includes('ROLE_USER')" class="font-medium">Déco</span>
        <span v-else class="font-medium">Connect</span>
      </button>
    </div>
</template>



<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { onMounted } from 'vue';
import CourseCard from './CourseCard.vue';



const user = ref({ username: '', roles: [] });

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