import { ref } from 'vue'
import axios from 'axios'
import { createOfflineDb } from './offlineDb'
import { useVisibilityFilter } from '../composables/ui/useVisibilityFilter'

// Références globales pour réactivité partagée
const menus = ref([])
const cats = ref([])
const user = ref({ username: "", roles: [] })
const exoMenus = ref([])
const exoContents = ref([])
const seoData = ref([])
const docDeCodes = ref([])

const clearUser = async () => {
  user.value = { username: '', roles: [] }
  await offlineDb.clearUser()
}

window.addEventListener('auth:logout', () => {
  clearUser().catch((error) => console.error('Impossible de supprimer l’utilisateur mis en cache.', error))
})

// Filtre de visibilité
const { filterByVisibility } = useVisibilityFilter()

// Accès à la base IndexedDB (voir offlineDb.js)
const offlineDb = createOfflineDb()
const saveToDB = offlineDb.saveAll
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

    await saveToDB('categories', cats.value)
    await saveToDB('menus', menus.value)

    console.log("Menus (en ligne):", menus.value)
  } catch {
    console.warn("Connexion échouée. Chargement hors ligne.")
    cats.value = await loadFromDB('categories')
    menus.value = await loadFromDB('menus')
    console.log("Menus (hors ligne):", menus.value)
  }
}

// ExoMenus (vrais menus d'exercices)
async function fetchExoMenus() {
  try {
    const [resCats, resExoMenus] = await Promise.all([
      axios.get("/api/categories"),
      axios.get("/api/exo_menus")
    ])

    // Filtrer les catégories et menus visibles
    cats.value = filterByVisibility(resCats.data.member)
    exoMenus.value = filterByVisibility(resExoMenus.data.member)

    await saveToDB('categories', cats.value)
    await saveToDB('exoMenus', exoMenus.value)

    console.log("ExoMenus (en ligne):", exoMenus.value)
  } catch {
    console.warn("Connexion échouée. Chargement hors ligne.")
    cats.value = await loadFromDB('categories')
    exoMenus.value = await loadFromDB('exoMenus')
    console.log("ExoMenus (hors ligne):", exoMenus.value)
  }
}

// ExoContents (tous les contenus d'exercices)
async function fetchExoContents() {
  try {
    const [resCats, resExoContents] = await Promise.all([
      axios.get("/api/categories"),
      axios.get("/api/exo_contents")
    ])

    // Filtrer les catégories et contenus visibles
    cats.value = filterByVisibility(resCats.data.member)
    exoContents.value = filterByVisibility(resExoContents.data.member)

    await saveToDB('categories', cats.value)
    await saveToDB('exoContents', exoContents.value)

    console.log("ExoContents (en ligne):", exoContents.value)
  } catch {
    console.warn("Connexion échouée. Chargement hors ligne.")
    cats.value = await loadFromDB('categories')
    exoContents.value = await loadFromDB('exoContents')
    console.log("ExoContents (hors ligne):", exoContents.value)
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
// fonction pour récuperer une page exo individuelle avec cache
async function fetchPageContentExo(pageId) {
  const url = `/api/exo_contents/${pageId}`

  try {
    // Vérifier le cache d'abord
    const cachedData = await getCachedApiResponse(url)
    if (cachedData) {
      console.log(`ExoPage ${pageId} (cache):`, cachedData)
      return cachedData
    }

    // Sinon, récupérer depuis l'API
    const response = await axios.get(url)
    const pageData = response.data

    // Sauvegarder dans le cache
    await cacheApiResponse(url, pageData)

    console.log(`ExoPage ${pageId} (en ligne):`, pageData)
    return pageData
  } catch (error) {
    console.warn(`Connexion échouée pour l'exercice ${pageId}. Chargement hors ligne.`)

    // Fallback vers le cache API, même expiré
    const cachedData = await getCachedApiResponse(url, Infinity)
    if (cachedData) {
      console.log(`ExoPage ${pageId} (hors ligne):`, cachedData)
      return cachedData
    }

    throw new Error(`Exercice ${pageId} non disponible hors ligne`, { cause: error })
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

// Récupérer tous les DocDeCode (liens vers documentations)
async function fetchDocDeCodes() {
  try {
    const response = await axios.get("/api/doc_de_codes")
    docDeCodes.value = response.data.member || response.data['hydra:member'] || []

    await saveToDB('docDeCodes', docDeCodes.value)

    console.log("DocDeCodes (en ligne):", docDeCodes.value)
  } catch {
    console.warn("Connexion échouée. Chargement DocDeCodes hors ligne.")
    docDeCodes.value = await loadFromDB('docDeCodes')
    console.log("DocDeCodes (hors ligne):", docDeCodes.value)
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

    // Liste des menus d'exercices (structure de navigation des exercices)
    exoMenus,

    // Tous les contenus d'exercices disponibles (exo_contents)
    exoContents,

    // Toutes les données SEO disponibles
    seoData,

    // Tous les liens de documentation disponibles (DocDeCode)
    docDeCodes,

    // ----------- Fonctions de récupération -----------

    // Récupère les pages de cours (page_contents) + catégories avec cache offline
    fetchMenus,

    // Récupère les infos de l'utilisateur connecté avec cache offline
    fetchUser,

    // Récupère une page de cours individuelle par son ID avec cache (1h)
    fetchPageContent,

    // Récupère un exercice individuel par son ID avec cache (1h)
    fetchPageContentExo,

    // Récupère les menus d'exercices (exo_menus) + catégories avec cache offline
    fetchExoMenus,

    // Récupère tous les contenus d'exercices (exo_contents) + catégories avec cache offline
    fetchExoContents,

    // Récupère toutes les données SEO avec cache offline
    fetchSeoData,

    // Récupère les données SEO d'une page spécifique par son identifiant avec cache (1h)
    fetchSeoByPage,

    // Récupère tous les liens de documentation (DocDeCode) avec cache offline
    fetchDocDeCodes
  }
}
