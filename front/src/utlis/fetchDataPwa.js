import { ref } from 'vue'
import axios from 'axios'
import { openDB } from 'idb'
import { useRouter } from 'vue-router'

// Références globales pour réactivité partagée
const menus = ref([])
const cats = ref([])
const user = ref({ username: "", roles: [] })

// Router (utile si besoin de redirection depuis le composable)
const router = useRouter()

// Création/connexion à la base IndexedDB
const dbPromise = openDB('spa-db', 2, {
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

    cats.value = resCats.data.member
    menus.value = resMenus.data.member

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

// ----------- Composable exporté -----------

export function useData() {
  return {
    menus,
    cats,
    user,
    fetchMenus,
    fetchUser,
    fetchPageContent
  }
}
