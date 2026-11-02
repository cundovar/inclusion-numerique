import { ref, computed } from 'vue'
import axios from 'axios'

// Apparence globale du site, chargée une fois et partagée par les composants
const siteDefaults = ref(null)

/**
 * Apparence du site définie dans le back-office (pas de personnalisation par visiteur)
 */
export function useCustomization() {
  const fetchSiteDefaults = async () => {
    try {
      const response = await axios.get('/api/site-configuration')
      siteDefaults.value = response.data.settings
      return siteDefaults.value
    } catch (error) {
      console.error('Erreur lors du chargement de la configuration du site:', error)
      return null
    }
  }

  const siteName = computed(() => siteDefaults.value?.siteName || 'Inclusion numérique')
  const header = computed(() => siteDefaults.value?.header || {})
  const body = computed(() => siteDefaults.value?.body || {})
  const menuGauche = computed(() => siteDefaults.value?.menuGauche || {})
  const menuDroit = computed(() => siteDefaults.value?.menuDroit || {})

  const initCustomization = async () => {
    if (!siteDefaults.value) {
      await fetchSiteDefaults()
    }
  }

  return {
    siteDefaults,
    siteName,
    header,
    body,
    menuGauche,
    menuDroit,
    fetchSiteDefaults,
    initCustomization,
  }
}
