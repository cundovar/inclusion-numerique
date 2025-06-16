<template>
  <div>
    <h2>Page Test API</h2>
    <p>Page accueil test</p>
    <p>bonjour Marin</p>
    <p v-if="message">Message API : {{ message }}</p>

    <div v-if="products.length">
      <h3>Liste des produits :</h3>
      <ul>
        <li v-for="product in products" :key="product.id">
          {{ product.nom }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const message = ref('');
const products = ref([]); // <== Déclarer un tableau réactif

onMounted(() => {
  fetch('/api/ping')
    .then(response => response.json())
    .then(data => {
      message.value = data.message;
      products.value = data.products; // <== Stocker les produits dans products
    })
    .catch(error => console.error(error));
});
</script>

<style scoped>
h2 {
  color: #42b983;
}
</style>
