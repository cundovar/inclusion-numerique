import { openDB } from 'idb'

// Base IndexedDB (NoSQL clé-valeur) utilisée pour le mode hors ligne de la PWA.
export const DB_NAME = 'spa-db'
export const DB_VERSION = 5

// Durée de validité du cache des réponses API (1 heure)
export const API_CACHE_TTL = 3600000

// Stores (collections) et leur clé primaire
export const STORES = {
  menus: 'id',
  categories: 'id',
  user: 'username',
  pages: 'id',
  api_cache: 'url',
  exoMenus: 'id',
  exoContents: 'id',
  seo: 'id',
  docDeCodes: 'id'
}

// Migrations du schéma : chaque version ajoute ses stores sans toucher aux précédents
export function upgradeSchema(db, oldVersion) {
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

// Supprime les proxys réactifs Vue : IndexedDB n'accepte que des objets clonables
const toPlain = (value) => JSON.parse(JSON.stringify(value))

export function createOfflineDb({ name = DB_NAME, now = () => Date.now() } = {}) {
  const dbPromise = openDB(name, DB_VERSION, { upgrade: upgradeSchema })

  return {
    dbPromise,

    // Sauvegarder un tableau dans un store donné
    async saveAll(store, array) {
      const db = await dbPromise
      const tx = db.transaction(store, 'readwrite')
      array.forEach(item => tx.store.put(toPlain(item)))
      await tx.done
    },

    // Lire tous les éléments d'un store
    async loadAll(store) {
      const db = await dbPromise
      return db.getAll(store)
    },

    // Utilisateur connecté (un seul enregistrement attendu, clé = username)
    async saveUser(userData) {
      const db = await dbPromise
      await db.put('user', toPlain(userData))
    },

    async loadUser() {
      const db = await dbPromise
      const allUsers = await db.getAll('user')
      return allUsers.length > 0 ? allUsers[0] : null
    },

    async clearUser() {
      const db = await dbPromise
      await db.clear('user')
    },

    // Page de cours individuelle (champs utiles à l'affichage hors ligne)
    async savePage(pageData) {
      const db = await dbPromise
      await db.put('pages', {
        id: pageData.id,
        slug: pageData.slug,
        title: pageData.title,
        content: pageData.content,
        cached_at: now()
      })
    },

    async loadPage(pageId) {
      const db = await dbPromise
      return db.get('pages', pageId)
    },

    // Cache générique des réponses API, indexé par URL
    async cacheApiResponse(url, data) {
      const db = await dbPromise
      await db.put('api_cache', { url, data: toPlain(data), cached_at: now() })
    },

    // Réponse en cache si plus récente que maxAge (Infinity = ignorer l'expiration)
    async getCachedApiResponse(url, maxAge = API_CACHE_TTL) {
      const db = await dbPromise
      const cached = await db.get('api_cache', url)
      if (cached && (now() - cached.cached_at) < maxAge) {
        return cached.data
      }
      return null
    }
  }
}
