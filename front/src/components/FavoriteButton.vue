<template>
  <button
    v-if="isAuthenticated"
    @click="toggleFavorite"
    :disabled="loading"
    :class="[
      'flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200 font-medium',
      isFavorite 
        ? 'bg-red-500 hover:bg-red-600 text-white' 
        : 'bg-gray-200 hover:bg-gray-300 text-gray-700',
      loading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
    ]"
  >
    <i 
      :class="[
        'text-sm',
        loading ? 'pi pi-spin pi-spinner' : (isFavorite ? 'pi pi-heart-fill' : 'pi pi-heart')
      ]"
    ></i>
    <span class="text-sm">
      {{ loading ? 'Chargement...' : (isFavorite ? 'Supprimer des favoris' : 'Ajouter aux favoris') }}
    </span>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  pageId: {
    type: Number,
    required: true
  }
})

const isFavorite = ref(false)
const loading = ref(false)
const isAuthenticated = ref(false)

// Vérifier si la page est en favori et si l'utilisateur est connecté
const checkFavorite = async () => {
  try {
    const response = await axios.get(`/api/me-favorites/check/${props.pageId}`, {
      withCredentials: true
    })
    isFavorite.value = response.data.isFavorite
    isAuthenticated.value = true
  } catch (error) {
    // Si l'utilisateur n'est pas connecté, ne pas afficher d'erreur
    if (error.response?.status === 401) {
      isFavorite.value = false
      isAuthenticated.value = false
    } else {
      console.error('Erreur lors de la vérification des favoris:', error)
    }
  }
}

// Basculer le statut de favori
const toggleFavorite = async () => {
  if (loading.value) return
  
  loading.value = true
  
  try {
    const response = await axios.post(`/api/me-favorites/toggle/${props.pageId}`, {}, {
      withCredentials: true
    })
    
    isFavorite.value = response.data.isFavorite
    
    // Afficher un message de succès (optionnel)
    if (response.data.action === 'added') {
      // Vous pouvez ajouter une notification ici
      console.log('Ajouté aux favoris')
    } else {
      console.log('Retiré des favoris')
    }
    
  } catch (error) {
    console.error('Erreur lors de la mise à jour des favoris:', error)
    
    // Gérer les erreurs spécifiques
    if (error.response?.status === 401) {
      // Rediriger vers la page de connexion
      window.location.href = '/login'
    } else if (error.response?.status === 409) {
      // Conflit - déjà en favori
      console.log('Cette page est déjà dans vos favoris')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  checkFavorite()
})
</script>