import { watch } from 'vue'
import { useRoute } from 'vue-router'

/**
 * Composable pour gérer les meta tags SEO côté client
 * Met à jour dynamiquement le titre et la description lors de la navigation
 */
export function useSeo() {
  const route = useRoute()

  /**
   * Met à jour les meta tags de la page
   * @param {Object} seoData - Les données SEO à appliquer
   * @param {string} seoData.title - Le titre de la page
   * @param {string} seoData.description - La description de la page
   * @param {string} [seoData.keywords] - Les mots-clés (optionnel)
   * @param {string} [seoData.ogImage] - L'image Open Graph (optionnel)
   */
  const updateMetaTags = (seoData) => {
    if (!seoData) return

    // Mise à jour du titre
    if (seoData.title) {
      document.title = seoData.title
    }

    // Mise à jour de la meta description
    updateMetaTag('name', 'description', seoData.description)

    // Mise à jour des meta keywords
    if (seoData.keywords) {
      updateMetaTag('name', 'keywords', seoData.keywords)
    }

    // Mise à jour des Open Graph tags
    updateMetaTag('property', 'og:title', seoData.title)
    updateMetaTag('property', 'og:description', seoData.description)

    if (seoData.ogImage) {
      updateMetaTag('property', 'og:image', seoData.ogImage)
    }

    // Mise à jour de l'URL canonique
    updateCanonicalUrl(window.location.href)
  }

  /**
   * Met à jour ou crée une meta tag
   * @param {string} attribute - L'attribut de sélection ('name' ou 'property')
   * @param {string} key - La clé de l'attribut
   * @param {string} value - La valeur du contenu
   */
  const updateMetaTag = (attribute, key, value) => {
    if (!value) return

    let element = document.querySelector(`meta[${attribute}="${key}"]`)

    if (!element) {
      element = document.createElement('meta')
      element.setAttribute(attribute, key)
      document.head.appendChild(element)
    }

    element.setAttribute('content', value)
  }

  /**
   * Met à jour l'URL canonique
   * @param {string} url - L'URL canonique
   */
  const updateCanonicalUrl = (url) => {
    let link = document.querySelector('link[rel="canonical"]')

    if (!link) {
      link = document.createElement('link')
      link.setAttribute('rel', 'canonical')
      document.head.appendChild(link)
    }

    link.setAttribute('href', url)
  }

  /**
   * Génère des meta tags SEO pour une page spécifique
   * @param {string} slug - Le slug de la page
   * @returns {Object} Les données SEO
   */
  const generateSeoForPage = (slug) => {
    const formattedSlug = slug
      .split('-')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')

    return {
      title: `${formattedSlug} - Cours et Documentation`,
      description: `Découvrez notre cours complet sur ${formattedSlug} avec exemples et exercices pratiques`,
      keywords: `${formattedSlug}, cours, tutoriel, documentation, formation`
    }
  }

  /**
   * Génère des meta tags SEO pour une catégorie
   * @param {string} category - Le nom de la catégorie
   * @returns {Object} Les données SEO
   */
  const generateSeoForCategory = (category) => {
    const formattedCategory = category.charAt(0).toUpperCase() + category.slice(1)

    return {
      title: `${formattedCategory} - Cours et Tutoriels`,
      description: `Découvrez nos cours et tutoriels sur ${formattedCategory}`,
      keywords: `${formattedCategory}, cours, tutoriel, formation`
    }
  }

  return {
    updateMetaTags,
    generateSeoForPage,
    generateSeoForCategory
  }
}
