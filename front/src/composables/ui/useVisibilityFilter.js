/**
 * Composable pour filtrer les données selon leur visibilité
 *
 * @description
 * Ce composable fournit une fonction réutilisable pour filtrer n'importe quel tableau
 * d'éléments en fonction de leur propriété 'visible'.
 * Si la propriété 'visible' est null, undefined ou true, l'élément sera considéré comme visible.
 * Seuls les éléments avec visible === false seront exclus.
 *
 * @example
 * // Utilisation dans un composant Vue
 * import { useVisibilityFilter } from '@/composables/ui/useVisibilityFilter'
 *
 * const { filterByVisibility } = useVisibilityFilter()
 *
 * // Filtrer des catégories
 * const visibleCategories = computed(() => filterByVisibility(categories.value))
 *
 * // Filtrer des menus ou autres données
 * const visibleMenus = computed(() => filterByVisibility(menus.value))
 */

import { computed } from 'vue'

export function useVisibilityFilter() {
  /**
   * Filtre un tableau d'éléments selon leur visibilité
   *
   * @param {Array} items - Le tableau d'éléments à filtrer
   * @param {string} [visibilityProp='visible'] - Le nom de la propriété de visibilité (par défaut: 'visible')
   * @returns {Array} Le tableau filtré contenant uniquement les éléments visibles
   *
   * @description
   * Un élément est considéré comme visible si:
   * - La propriété de visibilité est null
   * - La propriété de visibilité est undefined
   * - La propriété de visibilité est true
   *
   * Un élément est masqué uniquement si la propriété de visibilité est explicitement false
   *
   * @example
   * // Par défaut, utilise la propriété 'visible'
   * filterByVisibility(categories)
   *
   * // Utilise une propriété personnalisée
   * filterByVisibility(items, 'isActive')
   */
  const filterByVisibility = (items, visibilityProp = 'visible') => {
    if (!Array.isArray(items)) {
      console.warn('filterByVisibility: items must be an array', items)
      return []
    }

    return items.filter(item => {
      // Vérifier que l'item existe
      if (!item) {
        return false
      }

      // Récupérer la valeur de la propriété de visibilité
      const visibilityValue = item[visibilityProp]

      // Considérer comme visible si:
      // - null ou undefined (par défaut visible)
      // - explicitement true
      // Masquer uniquement si explicitement false
      return visibilityValue !== false
    })
  }

  /**
   * Crée un computed réactif qui filtre automatiquement selon la visibilité
   *
   * @param {import('vue').Ref} itemsRef - Ref contenant le tableau d'éléments
   * @param {string} [visibilityProp='visible'] - Le nom de la propriété de visibilité
   * @returns {import('vue').ComputedRef} Un computed qui retourne le tableau filtré
   *
   * @example
   * const categories = ref([...])
   * const visibleCategories = createVisibilityComputed(categories)
   */
  const createVisibilityComputed = (itemsRef, visibilityProp = 'visible') => {
    return computed(() => filterByVisibility(itemsRef.value, visibilityProp))
  }

  return {
    filterByVisibility,
    createVisibilityComputed
  }
}
