<template>
  <div class="flex items-center gap-2">
    <p v-if="logoutError" class="text-sm text-red-600" role="alert">{{ logoutError }}</p>
    <!-- Bouton profil -->
    <router-link to="/profile" v-if="user.roles.includes('ROLE_USER')">
      <AppButton
        variant="profile"
        size="sm"
        icon="pi pi-user"
        :text-content="user.username"
      />
    </router-link>

    <!-- Bouton connexion/déconnexion -->
    <AppButton
      @click="handleAuthAction"
      variant="auth"
      size="sm"
      :icon="user.roles.includes('ROLE_USER') ? 'pi pi-sign-out' : 'pi pi-sign-in'"
      :text-content="user.roles.includes('ROLE_USER') ? 'Déco' : 'Connect'"
    />
  </div>
</template>





<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import AppButton from '../../ui/AppButton.vue';
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
    console.log('User:', user.value);
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
