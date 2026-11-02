import { ref } from 'vue'
import axios from 'axios'
import { createOfflineDb } from './offlineDb'
import { useVisibilityFilter } from '../composables/ui/useVisibilityFilter'

// Références globales pour réactivité partagée
const menus = ref([])
const cats = ref([])
const user = ref({ username: "", roles: [] })
const seoData = ref([])

// Une personne était connectée : on efface aussi le contenu qui a pu lui être réservé.
// Un visiteur jamais connecté garde son catalogue hors ligne.
const clearUser = async () => {
  user.value = { username: '', roles: [] }
  const previous = await offlineDb.loadUser()
  if (previous?.username) {
    await offlineDb.clearPersonalData()
  } else {
    await offlineDb.clearUser()
  }
}

window.addEventListener('auth:logout', () => {
  clearUser().catch((error) => console.error('Impossible de supprimer l’utilisateur mis en cache.', error))
})

// Filtre de visibilité
const { filterByVisibility } = useVisibilityFilter()

// Accès à la base IndexedDB (voir offlineDb.js)
const offlineDb = createOfflineDb()
const saveToDB = offlineDb.saveAll
const replaceInDB = offlineDb.replaceAll
const loadFromDB = offlineDb.loadAll
const saveUserToDB = offlineDb.saveUser
const loadUserFromDB = offlineDb.loadUser
const savePageToDB = offlineDb.savePage
const loadPageFromDB = offlineDb.loadPage
const cacheApiResponse = offlineDb.cacheApiResponse
const getCachedApiResponse = offlineDb.getCachedApiResponse

// ----------- Fonctions de récupération API + fallback offline -----------

// Menus et catégories
async function fetchMenus() {
  try {
    const [resCats, resMenus] = await Promise.all([
      axios.get("/api/categories"),
      axios.get("/api/page_contents")
    ])

    // Filtrer les catégories et menus visibles
    cats.value = filterByVisibility(resCats.data.member)
    menus.value = filterByVisibility(resMenus.data.member)

    await replaceInDB('categories', cats.value)
    await replaceInDB('menus', menus.value)

    console.log("Menus (en ligne):", menus.value)
  } catch {
    console.warn("Connexion échouée. Chargement hors ligne.")
    cats.value = await loadFromDB('categories')
    menus.value = await loadFromDB('menus')
    console.log("Menus (hors ligne):", menus.value)
  }
}

// Utilisateur connecté
async function fetchUser() {
  try {
    const response = await axios.get("/user-api/me")
    user.value = response.data
    await saveUserToDB(user.value)
    console.log("Utilisateur (en ligne):", user.value)
  } catch (error) {
    if (!error.response) {
      console.warn("Pas de réponse — passage hors ligne.")
      user.value = await loadUserFromDB()
      console.log("Utilisateur (hors ligne):", user.value)
    } else if (error.response.status === 401) {
      await clearUser()
    } else {
      console.error("Erreur inattendue:", error)
    }
  }
}
// Fonction pour récupérer une page individuelle avec cache
async function fetchPageContent(pageId) {
  const url = `/api/page_contents/${pageId}`

  try {
    // Vérifier le cache d'abord
    const cachedData = await getCachedApiResponse(url)
    if (cachedData) {
      console.log(`Page ${pageId} (cache):`, cachedData)
      return cachedData
    }

    // Sinon, récupérer depuis l'API
    const response = await axios.get(url)
    const pageData = response.data

    // Sauvegarder dans le cache
    await cacheApiResponse(url, pageData)
    await savePageToDB(pageData)

    console.log(`Page ${pageId} (en ligne):`, pageData)
    return pageData
  } catch (error) {
    console.warn(`Connexion échouée pour la page ${pageId}. Chargement hors ligne.`)

    // Fallback vers IndexedDB
    const cachedPage = await loadPageFromDB(pageId)
    if (cachedPage) {
      console.log(`Page ${pageId} (hors ligne):`, cachedPage)
      return cachedPage
    }

    throw new Error(`Page ${pageId} non disponible hors ligne`, { cause: error })
  }
}

// Récupérer toutes les données SEO
async function fetchSeoData() {
  try {
    const response = await axios.get("/api/seos")
    seoData.value = response.data.member || response.data['hydra:member'] || []

    await saveToDB('seo', seoData.value)

    console.log("SEO Data (en ligne):", seoData.value)
  } catch {
    console.warn("Connexion échouée. Chargement SEO hors ligne.")
    seoData.value = await loadFromDB('seo')
    console.log("SEO Data (hors ligne):", seoData.value)
  }
}


// Récupérer les données SEO pour une page spécifique
async function fetchSeoByPage(pageName) {
  const url = `/api/seos?page=${pageName}`

  try {
    // Vérifier le cache d'abord
    const cachedData = await getCachedApiResponse(url)
    if (cachedData) {
      console.log(`SEO ${pageName} (cache):`, cachedData)
      return cachedData
    }

    // Sinon, récupérer depuis l'API
    const response = await axios.get(url)
    const seoPage = response.data.member?.[0] || response.data['hydra:member']?.[0] || null

    // Sauvegarder dans le cache
    await cacheApiResponse(url, seoPage)

    console.log(`SEO ${pageName} (en ligne):`, seoPage)
    return seoPage
  } catch {
    console.warn(`Connexion échouée pour SEO ${pageName}. Chargement hors ligne.`)

    // Fallback vers le cache API, même expiré
    const cachedData = await getCachedApiResponse(url, Infinity)
    if (cachedData) {
      console.log(`SEO ${pageName} (hors ligne):`, cachedData)
      return cachedData
    }

    // Fallback vers la recherche dans les données SEO chargées
    const allSeo = await loadFromDB('seo')
    const foundSeo = allSeo.find(s => s.page === pageName)
    if (foundSeo) {
      console.log(`SEO ${pageName} (IndexedDB):`, foundSeo)
      return foundSeo
    }

    return null
  }
}

// ----------- Composable exporté -----------

export function useData() {
  return {
    // ----------- États réactifs -----------

    // Contient tous les contenus de pages (page_contents) avec pagination
    menus,

    // Liste des catégories disponibles (symfony, vuejs, reactjs, etc.)
    cats,

    // Informations de l'utilisateur connecté (username, roles)
    user,

    // Toutes les données SEO disponibles
    seoData,

    // ----------- Fonctions de récupération -----------

    // Récupère les pages de cours (page_contents) + catégories avec cache offline
    fetchMenus,

    // Récupère les infos de l'utilisateur connecté avec cache offline
    fetchUser,

    // Récupère une page de cours individuelle par son ID avec cache (1h)
    fetchPageContent,

    // Récupère toutes les données SEO avec cache offline
    fetchSeoData,

    // Déconnexion locale : efface l'utilisateur et le contenu qui a pu lui être réservé (à attendre avant de quitter la page)
    clearUser,

    // Récupère les données SEO d'une page spécifique par son identifiant avec cache (1h)
    fetchSeoByPage
  }
}
