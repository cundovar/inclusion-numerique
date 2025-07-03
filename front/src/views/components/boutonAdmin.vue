<template>
<a v-if="user.roles.includes('ROLE_ADMIN')" 
   target="_blank" 
   href="http://localhost:8080/page-content" 
   class="cursor-pointer absolute text-amber-200 right-5 top-5">
  admin
</a>

</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
const user = ref({ username: '', roles: [] });

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
