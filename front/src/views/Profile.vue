<template>
  
  <div class="max-w-4xl mx-auto p-6 max-md:pb-96">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <!-- Header Profile -->
      <div class="bg-gradient-to-r from-blue-500 px-6 py-8">
        <div class="flex items-center space-x-4">
          <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center">
            <i class="pi pi-user text-3xl text-blue-500"></i>
          </div> 
          <div class="text-white">
            <h1 class="text-2xl font-bold">{{ user.username }}</h1>
            <p class="text-blue-100">{{ user.roles.includes('ROLE_ADMIN') ? 'Administrateur' : 'Utilisateur' }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation tabs -->
      <div class="border-b border-gray-200">
        <nav class="flex">
          <button
            @click="activeTab = 'favorites'"
            :class="[
              'px-6 py-3 border-b-2 font-medium text-sm',
              activeTab === 'favorites' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            <i class="pi pi-heart-fill mr-2"></i>
            Mes Favoris ({{ favorites.length }})
          </button>
          <button
            @click="activeTab = 'stats'"
            :class="[
              'px-6 py-3 border-b-2 font-medium text-sm',
              activeTab === 'stats' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            <i class="pi pi-chart-bar mr-2"></i>
            Statistiques
          </button>
        </nav>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Onglet Favoris -->
        <div v-if="activeTab === 'favorites'">
          <div v-if="loading" class="text-center py-8">
            <i class="pi pi-spinner pi-spin text-2xl text-blue-500"></i>
            <p class="text-gray-500 mt-2">Chargement de vos favoris...</p>
          </div>

          <div v-else-if="favorites.length === 0" class="text-center py-8">
            <i class="pi pi-heart text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun favori pour le moment</h3>
            <p class="text-gray-500">Explorez les cours et ajoutez vos préférés à vos favoris !</p>
          </div>

          <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div 
              v-for="favorite in favorites" 
              :key="favorite.id"
              class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold text-gray-800 text-sm">{{ favorite.title }}</h3>
                <button
                  @click="removeFavorite(favorite.id)"
                  class="text-red-500 hover:text-red-700 text-sm"
                >
                  <i class="pi pi-trash"></i>
                </button>
              </div>
              
              <p class="text-gray-600 text-sm mb-2">{{ favorite.category.name }}</p>
              
              <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">
                  Ajouté le {{ formatDate(favorite.createdAt) }}
                </span>
                
                <router-link
                  :to="`/pages${favorite.page.slug}`"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition-colors"
                >
                  Voir le cours
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Onglet Statistiques -->
        <div v-if="activeTab === 'stats'">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 rounded-lg p-6 text-center">
              <i class="pi pi-heart-fill text-3xl text-blue-500 mb-2"></i>
              <h3 class="text-2xl font-bold text-blue-600">{{ favorites.length }}</h3>
              <p class="text-blue-700">Cours favoris</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-6 text-center">
              <i class="pi pi-bookmark text-3xl text-green-500 mb-2"></i>
              <h3 class="text-2xl font-bold text-green-600">{{ categoryStats.length }}</h3>
              <p class="text-green-700">Catégories suivies</p>
            </div>
            
       
          </div>

          <!-- Répartition par catégorie -->
          <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Répartition par catégorie</h3>
            <div class="space-y-3">
              <div 
                v-for="cat in categoryStats" 
                :key="cat.name"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <span class="font-medium">{{ cat.name }}</span>
                <div class="flex items-center space-x-2">
                  <div class="w-32 bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                      :style="{ width: (cat.count / favorites.length * 100) + '%' }"
                    ></div>
                  </div>
                  <span class="text-sm text-gray-600">{{ cat.count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useData } from '../utlis/fetchDataPwa'
import axios from 'axios'

const { user } = useData()
const activeTab = ref('favorites')
const favorites = ref([])
const loading = ref(true)
axios.defaults.withCredentials = true;
// Récupérer les favoris
const fetchFavorites = async () => {
  try {
    const response = await axios.get('/api/me-favorites/list-my-favorites ')
    favorites.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des favoris:', error)
    if (error.response?.status === 401) {
      console.log('Utilisateur non authentifié, redirection vers login')
      // Optionnel : rediriger vers login
      // window.location.href = '/login'
    }
  } finally {
    loading.value = false
  }
}

// Supprimer un favori
const removeFavorite = async (favoriteId) => {
  try {
    await axios.delete(`/api/me-favorites/${favoriteId}`)
    favorites.value = favorites.value.filter(f => f.id !== favoriteId)
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

// Formater la date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR')
}

// Statistiques par catégorie
const categoryStats = computed(() => {
  const stats = {}
  favorites.value.forEach(favorite => {
    const categoryName = favorite.category.name
    stats[categoryName] = (stats[categoryName] || 0) + 1
  })
  
  return Object.entries(stats).map(([name, count]) => ({
    name,
    count
  })).sort((a, b) => b.count - a.count)
})



onMounted(() => {
  fetchFavorites()
})
</script>