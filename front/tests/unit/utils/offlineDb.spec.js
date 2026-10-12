import 'fake-indexeddb/auto'
import { describe, it, expect, beforeEach } from 'vitest'
import { openDB } from 'idb'
import { reactive } from 'vue'
import { createOfflineDb, DB_VERSION, STORES, API_CACHE_TTL } from '@/utils/offlineDb'

let counter = 0
let dbName
let clock
let offlineDb

beforeEach(() => {
  // Une base distincte par test : aucun état partagé
  dbName = `spa-db-test-${++counter}`
  clock = 1_000_000
  offlineDb = createOfflineDb({ name: dbName, now: () => clock })
})

describe('offlineDb — schéma', () => {
  it('crée tous les stores avec leur clé primaire', async () => {
    const db = await offlineDb.dbPromise

    expect(db.version).toBe(DB_VERSION)
    expect([...db.objectStoreNames].sort()).toEqual(Object.keys(STORES).sort())
    const tx = db.transaction(Object.keys(STORES))
    for (const [store, keyPath] of Object.entries(STORES)) {
      expect(tx.objectStore(store).keyPath).toBe(keyPath)
    }
  })

  // Stores présents dans les bases créées par les versions antérieures de l'application
  const LEGACY_STORES = {
    1: ['menus', 'categories', 'user'],
    2: ['menus', 'categories', 'user', 'pages', 'api_cache'],
    3: ['menus', 'categories', 'user', 'pages', 'api_cache', 'exoMenus', 'exoContents'],
    4: ['menus', 'categories', 'user', 'pages', 'api_cache', 'exoMenus', 'exoContents', 'seo']
  }

  it.each([1, 2, 3, 4])('migre une base en version %i sans perdre les données existantes', async (version) => {
    const name = `${dbName}-v${version}`
    const legacy = await openDB(name, version, {
      upgrade(db) {
        for (const store of LEGACY_STORES[version]) {
          db.createObjectStore(store, { keyPath: STORES[store] })
        }
      }
    })
    await legacy.put('menus', { id: 1, title: 'Flexbox' })
    legacy.close()

    const migrated = createOfflineDb({ name })
    const db = await migrated.dbPromise

    expect(db.version).toBe(DB_VERSION)
    expect([...db.objectStoreNames].sort()).toEqual(Object.keys(STORES).sort())
    expect(await migrated.loadAll('menus')).toEqual([{ id: 1, title: 'Flexbox' }])
  })
})

describe('offlineDb — collections', () => {
  it('enregistre puis relit une collection (upsert par id)', async () => {
    await offlineDb.saveAll('categories', [{ id: 1, name: 'symfony' }, { id: 2, name: 'vuejs' }])
    await offlineDb.saveAll('categories', [{ id: 2, name: 'vue' }])

    expect(await offlineDb.loadAll('categories')).toEqual([
      { id: 1, name: 'symfony' },
      { id: 2, name: 'vue' }
    ])
  })

  it('accepte les objets réactifs Vue (proxys non clonables)', async () => {
    await offlineDb.saveAll('menus', [reactive({ id: 3, tags: ['css'] })])

    expect(await offlineDb.loadAll('menus')).toEqual([{ id: 3, tags: ['css'] }])
  })
})

describe('offlineDb — utilisateur', () => {
  it('relit l’utilisateur enregistré puis l’efface à la déconnexion', async () => {
    expect(await offlineDb.loadUser()).toBeNull()

    await offlineDb.saveUser(reactive({ username: 'alice', roles: ['ROLE_USER'] }))
    expect(await offlineDb.loadUser()).toEqual({ username: 'alice', roles: ['ROLE_USER'] })

    await offlineDb.clearUser()
    expect(await offlineDb.loadUser()).toBeNull()
  })
})

describe('offlineDb — pages', () => {
  it('ne conserve que les champs utiles hors ligne, horodatés', async () => {
    await offlineDb.savePage({ id: 7, slug: 'flexbox', title: 'Flexbox', content: '<p>x</p>', author: 'ignored' })

    expect(await offlineDb.loadPage(7)).toEqual({
      id: 7, slug: 'flexbox', title: 'Flexbox', content: '<p>x</p>', cached_at: clock
    })
    expect(await offlineDb.loadPage(999)).toBeUndefined()
  })
})

describe('offlineDb — cache des réponses API', () => {
  const url = '/api/exo_contents/1'

  it('renvoie la réponse tant qu’elle a moins d’une heure', async () => {
    await offlineDb.cacheApiResponse(url, { id: 1 })
    clock += API_CACHE_TTL - 1

    expect(await offlineDb.getCachedApiResponse(url)).toEqual({ id: 1 })
  })

  it('ignore une réponse expirée, sauf si l’expiration est levée (hors ligne)', async () => {
    await offlineDb.cacheApiResponse(url, { id: 1 })
    clock += API_CACHE_TTL

    expect(await offlineDb.getCachedApiResponse(url)).toBeNull()
    expect(await offlineDb.getCachedApiResponse(url, Infinity)).toEqual({ id: 1 })
  })

  it('renvoie null pour une URL jamais mise en cache', async () => {
    expect(await offlineDb.getCachedApiResponse('/api/unknown', Infinity)).toBeNull()
  })
})
