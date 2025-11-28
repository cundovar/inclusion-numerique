import { ref, onMounted, onUnmounted } from 'vue'

/**
 * Composable pour générer et gérer un sommaire (Table of Contents) dynamique
 * Analyse les h2 et h3 du contenu et crée un menu de navigation
 *
 * @param {Ref} contentRef - Référence au conteneur du contenu HTML
 * @param {Object} options - Options de configuration
 * @returns {Object} - toc (sommaire), activeId (titre actif), scrollToHeading (fonction de scroll)
 */
export function useToc(contentRef, options = {}) {
  const {
    selectors = 'h2, h3', // Sélecteurs des titres à inclure
    offsetTop = 100, // Offset pour le scroll (header fixe)
    includeH3 = false // Inclure ou non les h3
  } = options

  const toc = ref([])
  const activeId = ref(null)
  let observer = null

  /**
   * Génère un slug depuis un texte
   */
  const slugify = (text) => {
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-')        // Remplacer espaces par -
      .replace(/[^\w\-]+/g, '')    // Supprimer caractères spéciaux
      .replace(/\-\-+/g, '-')      // Remplacer -- par -
      .replace(/^-+/, '')          // Trim - au début
      .replace(/-+$/, '')          // Trim - à la fin
  }

  /**
   * Génère le sommaire depuis les titres du contenu
   */
  const generateToc = () => {
    if (!contentRef.value) {
      console.warn('useToc: contentRef is not available')
      return
    }

    // Déterminer les sélecteurs à utiliser
    const selector = includeH3 ? 'h2, h3' : 'h2'

    // Récupérer tous les titres
    const headings = contentRef.value.querySelectorAll(selector)

    toc.value = Array.from(headings).map((heading, index) => {
      const text = heading.textContent.trim()
      const level = parseInt(heading.tagName.charAt(1)) // 2 pour h2, 3 pour h3

      // Créer un ID unique si absent
      let id = heading.id
      if (!id) {
        id = `${slugify(text)}-${index}`
        heading.id = id
      }

      return {
        id,
        text,
        level,
        element: heading
      }
    })

    console.log('📑 TOC généré:', toc.value.length, 'titres')

    // Démarrer l'observation des titres
    observeHeadings()
  }

  /**
   * Scroll vers un titre spécifique
   */
  const scrollToHeading = (id) => {
    const element = document.getElementById(id)
    if (element) {
      const top = element.getBoundingClientRect().top + window.pageYOffset - offsetTop

      window.scrollTo({
        top,
        behavior: 'smooth'
      })

      // Mettre à jour l'URL sans recharger la page
      if (history.pushState) {
        history.pushState(null, null, `#${id}`)
      }

      // Mettre à jour le titre actif
      activeId.value = id
    }
  }

  /**
   * Observer quel titre est actuellement visible (scroll spy)
   */
  const observeHeadings = () => {
    // Nettoyer l'observer précédent
    if (observer) {
      observer.disconnect()
    }

    // Configuration de l'Intersection Observer
    const options = {
      root: null,
      rootMargin: `-${offsetTop}px 0px -80% 0px`,
      threshold: 0
    }

    observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          activeId.value = entry.target.id
        }
      })
    }, options)

    // Observer tous les titres
    toc.value.forEach(({ element }) => {
      if (element) {
        observer.observe(element)
      }
    })
  }

  /**
   * Vérifier s'il y a un hash dans l'URL au chargement
   */
  const checkUrlHash = () => {
    const hash = window.location.hash
    if (hash) {
      const id = hash.substring(1)
      // Attendre que tout soit rendu avant de scroller
      setTimeout(() => {
        scrollToHeading(id)
      }, 300)
    }
  }

  /**
   * Recalculer le TOC (utile si le contenu change)
   */
  const refresh = () => {
    generateToc()
  }

  onMounted(() => {
    // Attendre que le contenu soit rendu
    setTimeout(() => {
      generateToc()
      checkUrlHash()
    }, 200)
  })

  onUnmounted(() => {
    if (observer) {
      observer.disconnect()
    }
  })

  return {
    toc,
    activeId,
    scrollToHeading,
    generateToc,
    refresh
  }
}
