<template>
  <button
    @click="handleAuthAction"
    class="cursor-pointer p-2 text-blue-950 hover:text-red-600 transition-colors"
  >
    <i v-if="user.roles && user.roles.includes('ROLE_USER')" class="text-2xl pi pi-sign-out"></i>
    <i v-else class="text-2xl pi pi-sign-in"></i>
  </button>
  <p v-if="logoutError" class="text-sm text-red-600" role="alert">{{ logoutError }}</p>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import { LOGOUT_EVENT, logout as logoutRequest } from '../../../services/auth.js';

const user = ref({ username: '', roles: [] });
const logoutError = ref('');

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
  logoutError.value = '';

  try {
    await logoutRequest();
    user.value = { username: '', roles: [] };
    window.location.assign('/login');
  } catch (error) {
    console.error('Erreur lors de la déconnexion', error);
    logoutError.value = 'La déconnexion a échoué. Vous êtes toujours connecté.';
  }
};

const clearUser = () => {
  user.value = { username: '', roles: [] };
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
  window.addEventListener(LOGOUT_EVENT, clearUser);
});

onBeforeUnmount(() => {
  window.removeEventListener(LOGOUT_EVENT, clearUser);
});
</script>
