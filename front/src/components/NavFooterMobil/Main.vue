<template>
  <footer class="min-w-[320px] p-3 md:p-4 rounded-t-2xl fixed bottom-0 left-0 right-0 h-20 md:h-24 bg-blue-300 z-[9999] shadow-2xl flex items-center justify-around xl:hidden gap-2 md:gap-4">

    <!-- Bouton Profil -->
    <router-link
    v-if="user"
      :to="`/${profileLink.name}`"
      class="shadow-neutral-600 shadow-lg p-2 md:p-3 rounded-2xl bg-blue-400 hover:bg-blue-500 w-14 md:w-20 h-14 md:h-16 text-white font-bold hover:underline flex flex-col items-center justify-center transition-all duration-200"
    >
      <i :class="profileLink.icon" class="text-2xl md:text-3xl mb-1"></i>
      <span class="text-xs md:text-sm">{{ profileLink.label }}</span>
    </router-link>
    <div class="flex items-center gap-2 flex-col border" v-else>
      <div>
        no  
      </div>
      <div>
        connecter
      </div>
    </div>
    <!-- Liens Technologies -->
    <router-link
      v-for="tech in techs"
      :key="tech.name"
      :to="`/pages/${tech.name}`"
      :class="[
        'shadow-neutral-600 shadow-lg p-2 md:p-3 rounded-xl w-14 md:w-20 h-14 md:h-16 text-white font-bold hover:underline flex flex-col items-center justify-center transition-all duration-200',
        tech.color === 'purple' ? 'bg-purple-400 hover:bg-purple-500' : 'bg-blue-400 hover:bg-blue-500'
      ]"
    >
      <i :class="tech.icon" class="text-2xl md:text-3xl mb-1"></i>
      <span class="text-xs md:text-sm">{{ tech.label }}</span>
    </router-link>

    <!-- Bouton Vue.js (masqué sur mobile, visible sur tablette) -->
    <router-link
      :to="`/${vuejsLink.name}`"
      class="hidden md:flex shadow-neutral-600 shadow-lg p-3 rounded-2xl bg-blue-400 hover:bg-blue-500 w-20 h-16 text-white font-bold hover:underline flex-col items-center justify-center transition-all duration-200"
    >
      <i :class="vuejsLink.icon" class="text-3xl mb-1"></i>
      <span class="text-sm">{{ vuejsLink.label }}</span>
    </router-link>

    <!-- Bouton Exercices (masqué sur mobile, visible sur tablette) -->
    <router-link
      to="/exercices"
      class="hidden md:flex shadow-neutral-600 shadow-lg p-3 rounded-2xl bg-green-300 hover:bg-green-500 w-20 h-16  font-bold hover:underline flex-col items-center justify-center transition-all duration-200"
    >
      <i class="pi pi-book text-3xl mb-1"></i>
      <span class="text-sm">Exos</span>
    </router-link>

  </footer>
</template>

<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { onMounted } from 'vue';

const user=ref(false)
const techs = [
  {
    name: 'symfony',
    label: 'Symfony',
    icon: 'fab fa-symfony'
  },
  {
    name: 'reactjs',
    label: 'React',
    icon: 'fab fa-react'
  },
  {
    name: 'WP',
    label: 'WP',
    icon: 'fab fa-wordpress'
  },
  {
    name: 'qcm',
    label: 'QCM',
    icon: 'fas fa-brain',
    color: 'purple'
  }
]

const profileLink = {
  name: 'profile',
  label: 'Profil',
  icon: 'pi pi-user'
}

const vuejsLink = {
  name: 'pages/vuejs',
  label: 'Vue.js',
  icon: 'fab fa-vuejs'
}


const fetchUser = async () => {
  try {
    const response = await axios.get('/user-api/me');
    user.value = response.data;
    if(user.value){
        user.value=true
    }
    console.log('User:', user.value);
  } catch (error) {
    console.error('Erreur lors de la récupération de l\'utilisateur', error);
    user.value=false
  }
};

onMounted(() => {
  fetchUser();
 
});
</script>



<style scoped>


.footer-mobil{
    z-index: 1000 !important;
}


</style>