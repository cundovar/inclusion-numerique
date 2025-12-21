<template>
  <div class="flex items-center gap-2">
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
import { ref } from 'vue';
import axios from 'axios';
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppButton from './commun/button/AppButton.vue';

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
    console.log('User:', user.value);
  } catch (error) {
    console.error('Erreur lors de la récupération de l\'utilisateur', error);
  }
};

onMounted(() => {
  fetchUser();
 
});

</script>