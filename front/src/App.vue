<template>
  <div>
    <h1>nav</h1>
    <nav>
      <router-link class="text-red-500" to="/">Accueil</router-link> |
      <router-link class="text-red-500" to="/test">Test</router-link> |
      <router-link class="text-red-500" to="/contact">Contact</router-link>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
</script>

<style scoped>

</style>
