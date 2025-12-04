import { ref, computed } from 'vue'
import axios from 'axios'

// État global de personnalisation
const customization = ref(null)
const allowedClasses = ref(null)
const loading = ref(false)

/**
 * Composable pour gérer la personnalisation utilisateur
 */
export function useCustomization() {
  /**
   * Charger la personnalisation depuis l'API
   */
  const fetchCustomization = async () => {
    loading.value = true
    try {
      const response = await axios.get('/api/customization')
      // Merger les settings avec les valeurs par défaut pour s'assurer que toutes les propriétés existent
      const defaults = getDefaultSettings()
      customization.value = {
        ...defaults,
        ...response.data.settings,
        header: { ...defaults.header, ...(response.data.settings.header || {}) },
        body: { ...defaults.body, ...(response.data.settings.body || {}) },
        menuGauche: { ...defaults.menuGauche, ...(response.data.settings.menuGauche || {}) },
        menuDroit: { ...defaults.menuDroit, ...(response.data.settings.menuDroit || {}) }
      }
      return customization.value
    } catch (error) {
      console.error('Erreur lors du chargement de la personnalisation:', error)
      // Utiliser les valeurs par défaut en cas d'erreur
      customization.value = getDefaultSettings()
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
   * Valeurs par défaut (fallback)
   */
  const getDefaultSettings = () => {
    return {
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
        categoryBgColor: 'bg-blue-300',
        categoryTextColor: 'text-gray-800',
        categoryTextSize: 'text-2xl',
        categoryHoverColor: 'hover:bg-blue-400',
        menuItemBgColor: 'bg-gray-100',
        menuItemTextColor: 'text-blue-500',
        menuItemHoverBgColor: 'hover:bg-gray-300',
      },
      menuDroit: {
        categoryBgColor: 'bg-blue-300',
        categoryTextColor: 'text-gray-600',
        categoryTextSize: 'text-xl',
        categoryHoverColor: 'hover:bg-blue-400',
      },
    }
  }

  /**
   * Computed pour faciliter l'accès aux settings
   */
  const siteName = computed(() => customization.value?.siteName || getDefaultSettings().siteName)
  const header = computed(() => customization.value?.header || getDefaultSettings().header)
  const body = computed(() => customization.value?.body || getDefaultSettings().body)
  const menuGauche = computed(() => customization.value?.menuGauche || getDefaultSettings().menuGauche)
  const menuDroit = computed(() => customization.value?.menuDroit || getDefaultSettings().menuDroit)

  /**
   * Initialiser la personnalisation au démarrage
   */
  const initCustomization = async () => {
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
    allowedClasses,
    loading,

    // Computed
    siteName,
    header,
    body,
    menuGauche,
    menuDroit,

    // Méthodes
    fetchCustomization,
    fetchAllowedClasses,
    saveCustomization,
    resetCustomization,
    initCustomization,
    getDefaultSettings,
  }
}
