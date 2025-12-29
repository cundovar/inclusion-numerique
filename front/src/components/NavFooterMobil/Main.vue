<template>
  <footer
    ref="footerRef"
    class="min-w-[320px] p-3 md:p-4 rounded-t-2xl fixed bottom-0 left-0 right-0 h-20 md:h-24 bg-blue-300 z-50 shadow-2xl flex items-center justify-around xl:hidden gap-2 md:gap-4">

    <!-- Bouton Profil -->
    <router-link
    v-if="user"
      :to="`/${profileLink.name}`"
      class="shadow-neutral-600 shadow-lg p-2 md:p-3 rounded-2xl nav-btn w-14 md:w-20 h-14 md:h-16 text-white font-bold hover:underline flex flex-col items-center justify-center transition-colors duration-200"
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
      :to="tech.name === 'qcm' ? '/qcm' : `/pages/${tech.name}`"
      :class="[
        'shadow-neutral-600 shadow-lg p-2 md:p-3 rounded-xl w-14 md:w-20 h-14 md:h-16 text-white font-bold hover:underline flex flex-col items-center justify-center transition-colors duration-200',
        tech.color === 'purple' ? 'nav-btn nav-btn--purple' : 'nav-btn'
      ]"
    >
      <i :class="tech.icon" class="text-2xl md:text-3xl mb-1"></i>
      <span class="text-xs md:text-sm">{{ tech.label }}</span>
    </router-link>

    <!-- Bouton Vue.js (masqué sur mobile, visible sur tablette) -->
    <router-link
      :to="`/${vuejsLink.name}`"
      class="hidden md:flex shadow-neutral-600 shadow-lg p-3 rounded-2xl nav-btn w-20 h-16 text-white font-bold hover:underline flex-col items-center justify-center transition-colors duration-200"
    >
      <i :class="vuejsLink.icon" class="text-3xl mb-1"></i>
      <span class="text-sm">{{ vuejsLink.label }}</span>
    </router-link>

    <!-- Bouton Exercices (masqué sur mobile, visible sur tablette) -->
    <router-link
      to="/exercices"
      class="hidden md:flex shadow-neutral-600 shadow-lg p-3 rounded-2xl nav-btn nav-btn--green w-20 h-16  font-bold hover:underline flex-col items-center justify-center transition-colors duration-200"
    >
      <i class="pi pi-book text-3xl mb-1"></i>
      <span class="text-sm">Exos</span>
    </router-link>

  </footer>
</template>

<script setup>
import axios from 'axios';
import { ref, onMounted, onUnmounted } from 'vue';
import Headroom from 'headroom.js';

const user = ref(false);
const footerRef = ref(null);
let headroom = null;
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

  // Headroom.js pour le footer (logique inversée)
  if (footerRef.value) {
    headroom = new Headroom(footerRef.value, {
      offset: 100,
      tolerance: {
        up: 10,
        down: 10
      },
      classes: {
        initial: 'footer-headroom',
        pinned: 'footer-headroom--pinned',
        unpinned: 'footer-headroom--unpinned',
        top: 'footer-headroom--top',
        notTop: 'footer-headroom--not-top'
      }
    });
    headroom.init();
  }
});

onUnmounted(() => {
  if (headroom) {
    headroom.destroy();
  }
});
</script>



<style scoped>

.nav-btn {
  background: #60a5fa;
}

.nav-btn:hover {
  background: #3b82f6;
}

.nav-btn:active,
.nav-btn:focus-visible {
  background: #2563eb;
}

.nav-btn--purple {
  background: #c084fc;
}

.nav-btn--purple:hover {
  background: #a855f7;
}

.nav-btn--purple:active,
.nav-btn--purple:focus-visible {
  background: #9333ea;
}

.nav-btn--green {
  background: #86efac;
}

.nav-btn--green:hover {
  background: #4ade80;
}

.nav-btn--green:active,
.nav-btn--green:focus-visible {
  background: #22c55e;
}

.footer-mobil{
    z-index: 1000 !important;
}
</style>

<style>
/* Headroom.js pour footer - logique inversée (glisse vers le bas) */
.footer-headroom {
  will-change: transform;
  transition: transform 0.3s ease-in-out !important;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

.footer-headroom--pinned {
  transform: translateY(0%) !important;
}

.footer-headroom--unpinned {
  transform: translateY(100%) !important;
}

.footer-headroom--top {
  transform: translateY(0%) !important;
}

.footer-headroom--not-top.footer-headroom--pinned {
  transform: translateY(0%) !important;
}

.footer-headroom--not-top.footer-headroom--unpinned {
  transform: translateY(100%) !important;
}
</style>
