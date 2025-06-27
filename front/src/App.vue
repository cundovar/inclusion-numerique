<template>
  <div>
    <h1>nav</h1>
    <nav>
 

      <div v-for="pageContent in pageContents" :key="pageContent.id">
        <router-link :to="`/pages${pageContent.page.slug}`">
          {{ pageContent.category.name }}
        </router-link>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const pageContents = ref([])

const fetchPageContents = async () => {
  try {
    const response = await axios.get('/api/page_contents')
    pageContents.value = response.data.member
    console.log(pageContents.value)
  } catch (error) {
    console.error('Erreur lors de la récupération des pageContents:', error)
  }
}

onMounted(() => {
  fetchPageContents()
})
</script>

<style scoped>
nav {
  margin-top: 10px;
}
</style>
