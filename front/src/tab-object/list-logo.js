import { computed } from 'vue'
import { useData } from '../utils/fetchDataPwa'

// Composable pour récupérer les logos depuis l'API
export function useLogos() {
  const { docDeCodes, fetchDocDeCodes } = useData()

  // Transformer les données de l'API au format attendu par les composants
  const logos = computed(() => {
    return docDeCodes.value.map(doc => ({
      src: doc.url,
      alt: doc.alt,
      icon: doc.logo?.logo || 'fas fa-file',
      name: doc.titre,
      color: doc.color
    }))
  })

  return {
    logos,
    fetchDocDeCodes
  }
}

// Export pour la rétrocompatibilité (fallback statique si l'API n'est pas chargée)
export const logos = []