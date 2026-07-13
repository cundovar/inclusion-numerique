import { ref, computed } from 'vue'
import axios from 'axios'

// État global de personnalisation
const customization = ref(null)
const siteDefaults = ref(null)
const allowedClasses = ref(null)
const loading = ref(false)

/**
 * Composable pour gérer la personnalisation utilisateur
 */
export function useCustomization() {
  /**
   * Charger les defaults du site depuis l'API
   */
  const fetchSiteDefaults = async () => {
    try {
      const response = await axios.get('/api/customization/defaults')
      siteDefaults.value = response.data.settings
      return siteDefaults.value
    } catch (error) {
      console.error('Erreur lors du chargement des defaults:', error)
      return null
    }
  }

  /**
   * Charger la personnalisation depuis l'API
   */
  const fetchCustomization = async () => {
    loading.value = true
    try {
      // D'abord charger les defaults si pas encore fait
      if (!siteDefaults.value) {
        await fetchSiteDefaults()
      }

      const response = await axios.get('/api/customization')
      customization.value = response.data.settings
      return customization.value
    } catch (error) {
      console.error('Erreur lors du chargement de la personnalisation:', error)
      // Utiliser les defaults du site
      if (siteDefaults.value) {
        customization.value = siteDefaults.value
      }
      return customization.value
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger les classes autorisées depuis l'API
   */
  const fetchAllowedClasses = async () => {
    try {
      const response = await axios.get('/api/customization/allowed-classes')
      allowedClasses.value = response.data.classes
      return allowedClasses.value
    } catch (error) {
      console.error('Erreur lors du chargement des classes autorisées:', error)
      return null
    }
  }

  /**
   * Sauvegarder la personnalisation
   */
  const saveCustomization = async (settings) => {
    loading.value = true
    try {
      const response = await axios.post('/api/customization', { settings })
      customization.value = response.data.settings
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Erreur lors de la sauvegarde:', error)
      return { success: false, error }
    } finally {
      loading.value = false
    }
  }

  /**
   * Réinitialiser aux valeurs par défaut
   */
  const resetCustomization = async () => {
    loading.value = true
    try {
      const response = await axios.post('/api/customization/reset')
      customization.value = response.data.settings
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Erreur lors de la réinitialisation:', error)
      return { success: false, error }
    } finally {
      loading.value = false
    }
  }

  /**
   * Computed pour faciliter l'accès aux settings
   */
  const siteName = computed(() => customization.value?.siteName || siteDefaults.value?.siteName || 'DevDoc')
  const header = computed(() => customization.value?.header || siteDefaults.value?.header || {})
  const body = computed(() => customization.value?.body || siteDefaults.value?.body || {})
  const menuGauche = computed(() => customization.value?.menuGauche || siteDefaults.value?.menuGauche || {})
  const menuDroit = computed(() => customization.value?.menuDroit || siteDefaults.value?.menuDroit || {})

  /**
   * Initialiser la personnalisation au démarrage
   */
  const initCustomization = async () => {
    // Toujours charger les defaults du site en premier
    if (!siteDefaults.value) {
      await fetchSiteDefaults()
    }
    // Puis charger la personnalisation utilisateur (si connecté)
    if (!customization.value) {
      await fetchCustomization()
    }
    if (!allowedClasses.value) {
      await fetchAllowedClasses()
    }
  }

  return {
    // État
    customization,
    siteDefaults,
    allowedClasses,
    loading,

    // Computed
    siteName,
    header,
    body,
    menuGauche,
    menuDroit,

    // Méthodes
    fetchSiteDefaults,
    fetchCustomization,
    fetchAllowedClasses,
    saveCustomization,
    resetCustomization,
    initCustomization,
  }
}
