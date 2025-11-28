<template>
  <button
    @click="handleAuthAction"
    class="cursor-pointer p-2 text-blue-950 hover:text-red-600 transition-colors"
  >
    <i v-if="user.roles && user.roles.includes('ROLE_USER')" class="text-2xl pi pi-sign-out"></i>
    <i v-else class="text-2xl pi pi-sign-in"></i>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const user = ref({ username: '', roles: [] });

const handleAuthAction = () => {
  if (user.value.roles && user.value.roles.includes('ROLE_USER')) {
    // Utilisateur connecté : déconnecter
    logout();
  } else {
    // Utilisateur non connecté : rediriger vers la page de login Symfony
    window.location.href = '/login';
  }
};

const logout = async () => {
  try {
    await axios.post('/logout');
    // Naviguer vers l'accueil puis recharger pour mettre à jour l'état
    await router.push('/');
    window.location.reload();
  } catch (error) {
    console.error('Erreur lors de la déconnexion', error);
  }
};

const fetchUser = async () => {
  try {
    const response = await axios.get('/user-api/me');
    user.value = response.data;
  } catch (error) {
    console.error('Erreur lors de la récupération de l\'utilisateur', error);
  }
};

onMounted(() => {
  fetchUser();
});
</script>
