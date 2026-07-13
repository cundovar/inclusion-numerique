import { ref } from 'vue'
import axios from 'axios'
import { openDB } from 'idb'
import { useRouter } from 'vue-router'
import { useVisibilityFilter } from '../composables/ui/useVisibilityFilter'

// Références globales pour réactivité partagée
const menus = ref([])
const cats = ref([])
const user = ref({ username: "", roles: [] })
const exoMenus = ref([])
const exoContents = ref([])
const seoData = ref([])
const docDeCodes = ref([])

// Router (utile si besoin de redirection depuis le composable)
const router = useRouter()

// Filtre de visibilité
const { filterByVisibility } = useVisibilityFilter()

// Création/connexion à la base IndexedDB
const dbPromise = openDB('spa-db', 5, {
  upgrade(db, oldVersion) {
    if (oldVersion < 1) {
      db.createObjectStore('menus', { keyPath: 'id' })
      db.createObjectStore('categories', { keyPath: 'id' })
      db.createObjectStore('user', { keyPath: 'username' })
    }
    if (oldVersion < 2) {
      db.createObjectStore('pages', { keyPath: 'id' })
      db.createObjectStore('api_cache', { keyPath: 'url' })
    }
    if (oldVersion < 3) {
      db.createObjectStore('exoMenus', { keyPath: 'id' })
      db.createObjectStore('exoContents', { keyPath: 'id' })
    }
    if (oldVersion < 4) {
      db.createObjectStore('seo', { keyPath: 'id' })
    }
    if (oldVersion < 5) {
      db.createObjectStore('docDeCodes', { keyPath: 'id' })
    }
  }
})

// ----------- Fonctions utilitaires de stockage local -----------

// Sauvegarder un tableau dans un store donné
async function saveToDB(store, array) {
  const db = await dbPromise
  const tx = db.transaction(store, 'readwrite')
  array.forEach(item => tx.store.put(JSON.parse(JSON.stringify(item))))
  await tx.done
}

// Lire tous les éléments depuis un store
async function loadFromDB(store) {
  const db = await dbPromise
  return db.getAll(store)
}

// Sauvegarder l'utilisateur dans IndexedDB
async function saveUserToDB(userData) {
  const db = await dbPromise
  const tx = db.transaction('user', 'readwrite')
  tx.store.put(JSON.parse(JSON.stringify(userData)))
  await tx.done
}






    

// Charger l'utilisateur depuis IndexedDB
async function loadUserFromDB() {
  const db = await dbPromise
  // Ici on suppose que l'utilisateur est stocké avec son `username` comme clé
  const allUsers = await db.getAll('user')
  return allUsers.length > 0 ? allUsers[0] : null
}

// Sauvegarder une page individuelle
async function savePageToDB(pageData) {
  const db = await dbPromise
  const tx = db.transaction('pages', 'readwrite')
  tx.store.put({
    id: pageData.id,
    slug: pageData.slug,
    title: pageData.title,
    content: pageData.content,
    cached_at: Date.now()
  })
  await tx.done
}

// Charger une page depuis IndexedDB
async function loadPageFromDB(pageId) {
  const db = await dbPromise
  return await db.get('pages', pageId)
}

// Cache générique pour les appels API
async function cacheApiResponse(url, data) {
  const db = await dbPromise
  const tx = db.transaction('api_cache', 'readwrite')
  tx.store.put({
    url: url,
    data: data,
    cached_at: Date.now()
  })
  await tx.done
}

// Récupérer une réponse API depuis le cache
async function getCachedApiResponse(url) {
  const db = await dbPromise
  const cached = await db.get('api_cache', url)
  // Cache valide pendant 1 heure
  if (cached && (Date.now() - cached.cached_at) < 3600000) {
    return cached.data
  }
  return null
}

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
  } catch (error) {
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
  } catch (error) {
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
  } catch (error) {
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
      console.error("Non authentifié — redirection.")
      router.push("/login")
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

    // Fallback vers le cache API
    const cachedData = await getCachedApiResponse(url)
    if (cachedData) {
      console.log(`ExoPage ${pageId} (hors ligne):`, cachedData)
      return cachedData
    }

    throw new Error(`Exercice ${pageId} non disponible hors ligne`)
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

    throw new Error(`Page ${pageId} non disponible hors ligne`)
  }
}

// Récupérer toutes les données SEO
async function fetchSeoData() {
  try {
    const response = await axios.get("/api/seos")
    seoData.value = response.data.member || response.data['hydra:member'] || []

    await saveToDB('seo', seoData.value)

    console.log("SEO Data (en ligne):", seoData.value)
  } catch (error) {
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
  } catch (error) {
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
  } catch (error) {
    console.warn(`Connexion échouée pour SEO ${pageName}. Chargement hors ligne.`)

    // Fallback vers le cache API
    const cachedData = await getCachedApiResponse(url)
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
