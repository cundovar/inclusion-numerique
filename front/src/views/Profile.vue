<template>
  <section  class="transition-all duration-500"
    :class="sectionSize ? 'playground-fullscreen z-[60] ' : 'playground'">


    <div class="mx-auto p-6 max-md:pb-96" :class="sectionSize ? 'playground-fullscreen z-[60] ' : 'max-w-4xl'">
    <AppButton v-if="sectionSize" variant="danger" text-content="X" @click="toggleSectionSize" />
    <AppButton v-else variant="danger" text-content="agrandire" @click="toggleSectionSize" />
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
        <AppButton variant="ghost" text-content="modifier profile" @click="openModal"/>
      </div>

      <AppModal v-model="isOpen"
       size="lg"
        title="Modifier le profile" 
        show-footer="true"
        @close="closeModal" >

<form @submit.prevent="updateProfile">
        <div>
          <AppInput v-model="user.username" label="Nouveau nom" />
         
        </div>
      </form>        

        <template #footer>

          <AppButton variant="secondary" text-content="Annuler" @click="closeModal" />
          <AppButton variant="primary" type="submit" text-content="Modifier" @click="updateProfile" />
        </template>
      </AppModal>

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
            @click="activeTab = 'notes'"
            :class="[
              'px-6 py-3 border-b-2 font-medium text-sm',
              activeTab === 'notes'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            <i class="pi pi-file-edit mr-2"></i>
            Mes Notes ({{ notes.length }})
          </button>
          <button
            v-if="pageTrackingEnabled"
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
          <button
            @click="activeTab = 'appearance'; console.log('Apparence clicked, activeTab:', activeTab)"
            :class="[
              'px-6 py-3 border-b-2 font-medium text-sm',
              activeTab === 'appearance'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            <i class="pi pi-palette mr-2"></i>
            Apparence
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

        <!-- Onglet Notes -->
        <div v-if="activeTab === 'notes'">
          <div v-if="loadingNotes" class="text-center py-8">
            <i class="pi pi-spinner pi-spin text-2xl text-blue-500"></i>
            <p class="text-gray-500 mt-2">Chargement de vos notes...</p>
          </div>

          <div v-else-if="notes.length === 0" class="text-center py-8">
            <i class="pi pi-file-edit text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucune note pour le moment</h3>
            <p class="text-gray-500">Prenez des notes sur vos cours pour les retrouver ici !</p>
          </div>

          <div v-else class="grid gap-4">
            <div
              v-for="note in notes"
              :key="note.id"
              class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-800 mb-1">{{ note.page.slug }}</h3>
                  <span class="text-xs text-gray-500">
                    Modifié le {{ formatDateTime(note.updatedAt) }}
                  </span>
                </div>
                <button
                  @click="deleteNote(note.id)"
                  class="text-red-500 hover:text-red-700 transition-colors ml-2"
                  title="Supprimer la note"
                >
                  <i class="pi pi-trash"></i>
                </button>
              </div>

              <div class="bg-white p-3 rounded border border-gray-200 mb-3">
                <p v-html="note.content" class="text-sm text-gray-700 whitespace-pre-wrap"></p>
              </div>

              <div class="flex justify-end">
                <router-link
                  :to="`/pages/${(note.page.slug || '').replace(/^\//, '')}`"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1"
                >
                  <i class="pi pi-external-link"></i>
                  Voir le cours
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Onglet Statistiques -->
        <div v-if="activeTab === 'stats' && pageTrackingEnabled">
          <!-- Contrôles de l'historique -->
          <div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
            <!-- Paramètre de tracking -->
            <div class="flex-1 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="font-semibold text-gray-800 mb-1">Suivi de navigation</h3>
                  <p class="text-sm text-gray-600">
                    {{ trackingEnabled ? 'Vos visites sont enregistrées' : 'Le suivi est désactivé' }}
                  </p>
                </div>
                <AppButton
                  @click="toggleTracking"
                  :variant="trackingEnabled ? 'danger' : 'success'"
                  :text-content="trackingEnabled ? 'Désactiver' : 'Activer'"
                />
            
              </div>
            </div>

            <!-- Bouton effacer historique -->
            <AppButton
              @click="clearAllHistory"
              variant="danger"
              text-content="Effacer l'historique"
              icon="pi pi-trash"
            />
          
          </div>

          <div class="grid grid-cols-1 gap-6">
            <div class="bg-purple-50 rounded-lg p-6 text-center">
              <i class="pi pi-history text-3xl text-purple-500 mb-2"></i>
              <h3 class="text-2xl font-bold text-purple-600">{{ pageVisits.length }}</h3>
              <p class="text-purple-700">Pages visitées</p>
            </div>
          </div>

          <!-- Historique détaillé des visites -->
          <div v-if="pageVisits.length > 0" class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Historique des visites</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Page
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date de visite
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Durée
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr
                    v-for="visit in pageVisits"
                    :key="visit.id"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-4 py-3">
                      <router-link
                        :to="`/pages/${(visit.page?.slug || '').replace(/^\//, '')}`"
                        class="text-blue-600 hover:text-blue-800 font-medium"
                      >
                        {{ visit.page?.title || visit.page?.slug || 'Sans titre' }}
                      </router-link>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                      {{ formatDateTime(visit.visitedAt) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                      {{ formatDuration(visit.timeSpent) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                      <button
                        @click="removeVisit(visit.id)"
                        class="text-red-500 hover:text-red-700 transition-colors"
                      >
                        <i class="pi pi-trash"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Répartition par catégorie (favoris) -->
          <div v-if="categoryStats.length > 0" class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Répartition des favoris par catégorie</h3>
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

        <!-- Onglet Apparence -->
        <div v-if="activeTab === 'appearance'">
          <div v-if="!allowedClasses || !customSettings.header || !customSettings.body" class="text-center py-8">
            <i class="pi pi-spinner pi-spin text-2xl text-blue-500"></i>
            <p class="text-gray-500 mt-2">Chargement des options de personnalisation...</p>
          </div>

          <div v-else>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Personnaliser l'apparence</h2>
            <router-link to="/components-demo">
              <AppButton
                variant="outline"
                icon="pi pi-palette"
                size="sm"
                text-content="Voir tous les composants"
              />
            </router-link>
          </div>

          <!-- Nom du site -->
          <div class="mb-8 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
              <i class="pi pi-globe mr-2 text-blue-500"></i>
              Nom du site
            </h3>
            <div>
              <AppInput
                v-model="customSettings.siteName"
                type="text"
                label="Nom du site (50 caractères max)"
                placeholder="DevDoc"
                :maxlength="50"
                clearable
              />
            </div>
          </div>

          <!-- Header -->
          <div class="mb-8 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
              <i class="pi pi-window-maximize mr-2 text-blue-500"></i>
              Header (En-tête)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Header - Couleur de fond -->
              <AppSelect
                v-model="customSettings.header.bgColor"
                label="Couleur de fond"
                :options="allowedClasses?.bgColors || []"
                clearable
              />

              <!-- Header - Couleur de texte -->
              <AppSelect
                v-model="customSettings.header.textColor"
                label="Couleur de texte"
                :options="allowedClasses?.textColors || []"
                clearable
              />

              <!-- Header - Couleur hover -->
              <AppSelect
                v-model="customSettings.header.hoverColor"
                label="Couleur hover"
                :options="allowedClasses?.hoverBgColors || []"
                clearable
              />
            </div>
          </div>

          <!-- Body -->
          <div class="mb-8 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
              <i class="pi pi-file mr-2 text-blue-500"></i>
              Body (Corps de page)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Body - Couleur de fond -->
              <AppSelect
                v-model="customSettings.body.bgColor"
                label="Couleur de fond"
                :options="allowedClasses?.bgColors || []"
                clearable
              />

              <!-- Body - Couleur de texte -->
              <AppSelect
                v-model="customSettings.body.textColor"
                label="Couleur de texte"
                :options="allowedClasses?.textColors || []"
                clearable
              />
            </div>
          </div>

          <!-- Menu Gauche -->
          <div class="mb-8 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
              <i class="pi pi-bars mr-2 text-blue-500"></i>
              Menu Gauche (Navigation)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Catégorie - Couleur de fond -->
              <AppSelect
                v-model="customSettings.menuGauche.categoryBgColor"
                label="Catégorie - Couleur de fond"
                :options="allowedClasses?.bgColors || []"
                clearable
              />

              <!-- Catégorie - Couleur de texte -->
              <AppSelect
                v-model="customSettings.menuGauche.categoryTextColor"
                label="Catégorie - Couleur de texte"
                :options="allowedClasses?.textColors || []"
                clearable
              />

              <!-- Catégorie - Taille de texte -->
              <AppSelect
                v-model="customSettings.menuGauche.categoryTextSize"
                label="Catégorie - Taille de texte"
                :options="allowedClasses?.textSizes || []"
                clearable
              />

              <!-- Catégorie - Couleur hover -->
              <AppSelect
                v-model="customSettings.menuGauche.categoryHoverColor"
                label="Catégorie - Couleur hover"
                :options="allowedClasses?.hoverBgColors || []"
                clearable
              />

              <!-- Menu Item - Couleur de fond -->
              <AppSelect
                v-model="customSettings.menuGauche.menuItemBgColor"
                label="Menu Item - Couleur de fond"
                :options="allowedClasses?.bgColors || []"
                clearable
              />

              <!-- Menu Item - Couleur de texte -->
              <AppSelect
                v-model="customSettings.menuGauche.menuItemTextColor"
                label="Menu Item - Couleur de texte"
                :options="allowedClasses?.textColors || []"
                clearable
              />

              <!-- Menu Item - Couleur hover -->
              <AppSelect
                v-model="customSettings.menuGauche.menuItemHoverBgColor"
                label="Menu Item - Couleur hover fond"
                :options="allowedClasses?.hoverBgColors || []"
                clearable
              />
            </div>
          </div>

          <!-- Menu Droit -->
          <div class="mb-8 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
              <i class="pi pi-bars mr-2 text-green-500"></i>
              Menu Droit
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Catégorie - Couleur de fond -->
              <AppSelect
                v-model="customSettings.menuDroit.categoryBgColor"
                label="Catégorie - Couleur de fond"
                :options="allowedClasses?.bgColors || []"
                clearable
              />

              <!-- Catégorie - Couleur de texte -->
              <AppSelect
                v-model="customSettings.menuDroit.categoryTextColor"
                label="Catégorie - Couleur de texte"
                :options="allowedClasses?.textColors || []"
                clearable
              />

              <!-- Catégorie - Taille de texte -->
              <AppSelect
                v-model="customSettings.menuDroit.categoryTextSize"
                label="Catégorie - Taille de texte"
                :options="allowedClasses?.textSizes || []"
                clearable
              />

              <!-- Catégorie - Couleur hover -->
              <AppSelect
                v-model="customSettings.menuDroit.categoryHoverColor"
                label="Catégorie - Couleur hover"
                :options="allowedClasses?.hoverBgColors || []"
                clearable
              />
            </div>
          </div>

          <!-- Boutons d'action -->
          <div class="flex gap-4">
            <AppButton
              @click="saveAppearance"
              :disabled="savingAppearance"
              :loading="savingAppearance"
              variant="primary"
              size="lg"
              icon="pi-save"
              :text-content="savingAppearance ? 'Enregistrement...' : 'Enregistrer'"
            />

            <AppButton
              @click="resetAppearance"
              :disabled="savingAppearance"
              variant="secondary"
              size="lg"
              icon="pi-refresh"
              text-content="Réinitialiser"
            />
          </div>

          <!-- Message de succès -->
          <AppAlert
            v-if="appearanceMessage"
            type="success"
            variant="light"
            :message="appearanceMessage"
            :dismissible="false"
          />
          </div>
        </div>
      </div>
    </div>
  </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useData } from '../utlis/fetchDataPwa'
import { useCustomization } from '../composables/useCustomization'
import AppButton from '../components/commun/button/AppButton.vue'
import AppAlert from '../components/commun/AppAlert.vue'
import AppInput from '../components/commun/input/AppInput.vue'
import AppSelect from '../components/commun/select/AppSelect.vue'
import axios from 'axios'
import AppModal from '../components/commun/modal/AppModal.vue'

const pageTrackingEnabled = import.meta.env.VITE_ENABLE_PAGE_TRACKING === 'true'

const { user } = useData()
console.log("data",useData())
console.log("user",user.value)
const activeTab = ref('favorites')
const favorites = ref([])
const loading = ref(true)
const pageVisits = ref([])
const loadingHistory = ref(true)
const trackingEnabled = ref(pageTrackingEnabled)
const notes = ref([])
const loadingNotes = ref(true)
axios.defaults.withCredentials = true;
// open modal
const isOpen = ref(false)
const openModal = () => {
  isOpen.value = true
}
const closeModal = () => {
  isOpen.value = false
}
console.log("user id",user.value)
//modifier profile
const updateProfile =async () => {
  try{

await axios.patch(`/api/users/${user.value.id}`, {
      username: user.value.username,
     
    })
    isOpen.value = false
  }catch(error){
    console.error(error)
  }}



// Customization
const {
  customization,
  allowedClasses,
  fetchCustomization,
  fetchAllowedClasses,
  saveCustomization,
  resetCustomization,
  getDefaultSettings
} = useCustomization()

const customSettings = ref({
  siteName: 'DevDoc',
  header: {
    bgColor: '',
    textColor: '',
    hoverColor: '',
  },
  body: {
    bgColor: '',
    textColor: '',
  },
  menuGauche: {
    categoryBgColor: '',
    categoryTextColor: '',
    categoryTextSize: '',
    categoryHoverColor: '',
    menuItemBgColor: '',
    menuItemTextColor: '',
    menuItemHoverBgColor: '',
  },
  menuDroit: {
    categoryBgColor: '',
    categoryTextColor: '',
    categoryTextSize: '',
    categoryHoverColor: '',
  }
})
const savingAppearance = ref(false)
const appearanceMessage = ref('')
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


  //resize section
  const sectionSize= ref(false)
  const toggleSectionSize = () => {
    sectionSize.value = !sectionSize.value
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

// Récupérer l'historique des pages visitées
const fetchPageVisits = async () => {
  if (!pageTrackingEnabled) {
    return
  }

  try {
    const response = await axios.get('/api/page-visits/my-history')
    pageVisits.value = response.data

    // console.log('=== DONNÉES REÇUES DE L\'API ===')
    // console.log('Nombre de visites:', response.data.length)
    // console.log('Données complètes:', JSON.stringify(response.data, null, 2))

    // response.data.forEach((visit, index) => {
      // console.log(`\n========== Visite ${index + 1} ==========`)
      // console.log('Objet complet:', visit)
      // console.log('  - ID:', visit.id)
      // console.log('  - Page title:', visit.page?.title)
      // console.log('  - Page slug:', visit.page?.slug)
      // console.log('  - visitedAt:', visit.visitedAt)
      // console.log('  - timeSpent:', visit.timeSpent)
      // console.log('================================')
    // })

    // console.log('\n=== FIN DES DONNÉES ===')
  } catch (error) {
    console.error('Erreur lors du chargement de l\'historique:', error)
  } finally {
    loadingHistory.value = false
  }
}

// Récupérer le statut du tracking
const fetchTrackingStatus = async () => {
  if (!pageTrackingEnabled) {
    trackingEnabled.value = false
    return
  }

  try {
    const response = await axios.get('/api/page-visits/tracking-status')
    trackingEnabled.value = response.data.trackingEnabled
  } catch (error) {
    console.error('Erreur lors du chargement du statut:', error)
  }
}

// Supprimer une visite
const removeVisit = async (visitId) => {
  if (!pageTrackingEnabled) {
    return
  }

  try {
    await axios.delete(`/api/page-visits/${visitId}`)
    pageVisits.value = pageVisits.value.filter(v => v.id !== visitId)
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

// Effacer tout l'historique
const clearAllHistory = async () => {
  if (!pageTrackingEnabled) {
    return
  }

  if (!confirm('Êtes-vous sûr de vouloir effacer tout votre historique ?')) {
    return
  }

  try {
    await axios.delete('/api/page-visits/clear-all')
    pageVisits.value = []
  } catch (error) {
    console.error('Erreur lors de l\'effacement:', error)
  }
}

// Activer/désactiver le tracking
const toggleTracking = async () => {
  if (!pageTrackingEnabled) {
    return
  }

  try {
    const response = await axios.post('/api/page-visits/toggle-tracking', {
      enabled: !trackingEnabled.value
    })
    trackingEnabled.value = response.data.trackingEnabled
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
  }
}

// Récupérer les notes de l'utilisateur
const fetchNotes = async () => {
  try {
    console.log('=== FETCH NOTES CALLED ===')
    const response = await axios.get('/api/notes/my-notes')
    console.log('Notes reçues de l\'API:', response.data)
    notes.value = response.data
    console.log('Notes dans notes.value:', notes.value)
  } catch (error) {
    console.error('Erreur lors du chargement des notes:', error)
    if (error.response) {
      console.error('Status:', error.response.status)
      console.error('Data:', error.response.data)
    }
  } finally {
    loadingNotes.value = false
  }
}

// Supprimer une note
const deleteNote = async (noteId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette note ?')) {
    return
  }

  try {
    await axios.delete(`/api/notes/${noteId}`)
    notes.value = notes.value.filter(n => n.id !== noteId)
  } catch (error) {
    console.error('Erreur lors de la suppression de la note:', error)
  }
}

// Formater la date
const formatDate = (dateString) => {
  if (!dateString) return 'Date inconnue'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return 'Date invalide'
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Formater la date et heure
const formatDateTime = (dateString) => {
  if (!dateString) return 'Date inconnue'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return 'Date invalide'
  return date.toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

// Formater la durée
const formatDuration = (seconds) => {
  if (!seconds || seconds === null) return 'N/A'

  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60

  if (hours > 0) {
    return `${hours}h ${minutes}m ${secs}s`
  } else if (minutes > 0) {
    return `${minutes}m ${secs}s`
  } else {
    return `${secs}s`
  }
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

// Sauvegarder l'apparence
const saveAppearance = async () => {
  savingAppearance.value = true
  appearanceMessage.value = ''

  try {
    const result = await saveCustomization(customSettings.value)

    if (result.success) {
      appearanceMessage.value = 'Apparence sauvegardée avec succès !'
      setTimeout(() => {
        appearanceMessage.value = ''
      }, 3000)
    } else {
      appearanceMessage.value = 'Erreur lors de la sauvegarde'
      setTimeout(() => {
        appearanceMessage.value = ''
      }, 3000)
    }
  } catch (error) {
    console.error('Erreur lors de la sauvegarde de l\'apparence:', error)
    appearanceMessage.value = 'Erreur lors de la sauvegarde'
    setTimeout(() => {
      appearanceMessage.value = ''
    }, 3000)
  } finally {
    savingAppearance.value = false
  }
}

// Réinitialiser l'apparence aux valeurs par défaut
const resetAppearance = async () => {
  if (!confirm('Êtes-vous sûr de vouloir réinitialiser l\'apparence aux valeurs par défaut ?')) {
    return
  }

  savingAppearance.value = true
  appearanceMessage.value = ''

  try {
    const result = await resetCustomization()

    if (result.success) {
      customSettings.value = result.data.settings
      appearanceMessage.value = 'Apparence réinitialisée avec succès !'
      setTimeout(() => {
        appearanceMessage.value = ''
      }, 3000)
    } else {
      appearanceMessage.value = 'Erreur lors de la réinitialisation'
      setTimeout(() => {
        appearanceMessage.value = ''
      }, 3000)
    }
  } catch (error) {
    console.error('Erreur lors de la réinitialisation:', error)
    appearanceMessage.value = 'Erreur lors de la réinitialisation'
    setTimeout(() => {
      appearanceMessage.value = ''
    }, 3000)
  } finally {
    savingAppearance.value = false
  }
}



onMounted(async () => {
  fetchFavorites()
  if (pageTrackingEnabled) {
    fetchPageVisits()
    fetchTrackingStatus()
  } else {
    loadingHistory.value = false
    trackingEnabled.value = false
  }
  fetchNotes()

  // Charger la personnalisation
  const settings = await fetchCustomization()
  if (settings) {
    customSettings.value = settings
  } else {
    customSettings.value = getDefaultSettings()
  }

  // Charger les classes autorisées
  await fetchAllowedClasses()
})
</script>
<style scoped>
 .playground {
    position: relative;
    width: 100%;
    margin: 0 auto;
    padding: 16px;
  }

  /* Mode plein écran */
  .playground-fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;

    background: #fff;
    padding: 16px;
    margin: 0;
    overflow: auto;
  }

</style>
