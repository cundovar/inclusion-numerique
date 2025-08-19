<template>

<footer class=" min-w-[320px] p-2 rounded-t-2xl fixed bottom-0 left-0 right-0 h-20 bg-blue-300 z-[9999] shadow-md flex items-center justify-around xl:hidden">

  <router-link
    :to="`/${profileLink.name}`"
    class=" shadow-neutral-600 shadow-lg p-2  rounded-2xl bg-blue-400 w-12  text-white font-bold hover:underline flex flex-col items-center justify-center"
  >
    <i :class="profileLink.icon" class="text-2xl mb-1"></i>
    <span class="text-xs">{{ profileLink.label }}</span>
  </router-link>

  <router-link
    :to="`/${qcmLink.name}`"
    class=" shadow-neutral-600 shadow-lg p-2  rounded-2xl bg-violet-400 w-12  text-white font-bold hover:underline flex flex-col items-center justify-center"
  >
    <i :class="qcmLink.icon" class="text-2xl mb-1"></i>
    <span class="text-xs">{{ qcmLink.label }}</span>
  </router-link>
  
    <router-link
      v-for="tech in techs"
      :key="tech.name"
      :to="`/pages/${tech.name}`"
      class=" shadow-neutral-600 shadow-lg p-2 rounded-xl bg-blue-400 w-12  text-white font-bold hover:underline flex flex-col items-center justify-center"
    >
      <i :class="tech.icon" class="text-2xl mb-1"></i>
      <span class="text-xs">{{ tech.label }}</span>
    </router-link>
    


</footer> 
</template>

<script setup>
const techs = [
  {
    name: 'symfony',
    label: 'Symfony',
    icon: 'fab fa-symfony'
  },
  {
    name: 'vuejs',
    label: 'Vue.js',
    icon: 'fab fa-vuejs'
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
  }
]

const profileLink = {
  name: 'profile',
  label: 'Profil',
  icon: 'pi pi-user'
}

const qcmLink = {
  name: 'qcm',
  label: 'QCM',
  icon: 'fas fa-brain'
}

</script>



<style scoped>


.footer-mobil{
    z-index: 1000 !important;
}


</style>