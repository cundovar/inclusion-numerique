import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { ref } from 'vue'

const menus = ref([])
const fetchMenus = vi.fn()
vi.mock('@/utils/fetchDataPwa', () => ({ useData: () => ({ menus, fetchMenus }) }))
const chargerCaptures = vi.fn()
vi.mock('@/services/captures', () => ({ chargerCaptures: (ids) => chargerCaptures(ids) }))

const { toutTelecharger, dernierTelechargement, HorsLigneError, PAQUET } = await import('@/services/telechargementHorsLigne')

let enCache
let add

beforeEach(() => {
  localStorage.clear()
  fetchMenus.mockReset().mockImplementation(async () => {
    menus.value = [
      { id: 1, code: '<p>[capture:1]</p><p>[capture:2]</p>' },
      { id: 2, code: '<p>[capture:2]</p>' },
      { id: 3, code: null }
    ]
  })
  chargerCaptures.mockReset().mockImplementation(async (ids) =>
    ids.map((id) => ({ id, url: `/uploads/course-media/${id}.png` })))
  enCache = new Set(['/uploads/course-media/1.png'])
  add = vi.fn(async (url) => { enCache.add(url) })
  vi.stubGlobal('caches', {
    open: async (name) => {
      expect(name).toBe('captures-images')
      return { match: async (url) => (enCache.has(url) ? {} : undefined), add }
    }
  })
})

afterEach(() => vi.unstubAllGlobals())

describe('Tout télécharger pour le hors ligne', () => {
  it('rafraîchit les cours, charge chaque capture une seule fois et range les images manquantes', async () => {
    const progression = []

    const resultat = await toutTelecharger({ onProgress: (f, t) => progression.push([f, t]), now: () => 1700000000000 })

    expect(fetchMenus).toHaveBeenCalled()
    expect(chargerCaptures).toHaveBeenCalledWith([1, 2])
    expect(add).toHaveBeenCalledTimes(1)
    expect(add).toHaveBeenCalledWith('/uploads/course-media/2.png')
    expect(resultat).toEqual({ cours: 3, captures: 2, images: 2, echecs: 0 })
    expect(progression.at(-1)).toEqual([3, 3])
    expect(dernierTelechargement()).toEqual(new Date(1700000000000))
  })

  it('demande les légendes par paquets pour garder des adresses courtes', async () => {
    fetchMenus.mockImplementation(async () => {
      menus.value = [{ id: 1, code: Array.from({ length: PAQUET + 1 }, (_, i) => `[capture:${i + 1}]`).join(' ') }]
    })

    await toutTelecharger()

    expect(chargerCaptures).toHaveBeenCalledTimes(2)
    expect(chargerCaptures.mock.calls[1][0]).toEqual([PAQUET + 1])
  })

  it('compte les images en échec sans tout arrêter, et ne note pas le téléchargement comme complet', async () => {
    add.mockRejectedValue(new Error('404'))

    const resultat = await toutTelecharger()

    expect(resultat.echecs).toBe(1)
    expect(resultat.images).toBe(1)
    expect(dernierTelechargement()).toBeNull()
  })

  it('refuse de commencer sans connexion', async () => {
    vi.spyOn(navigator, 'onLine', 'get').mockReturnValue(false)

    await expect(toutTelecharger()).rejects.toBeInstanceOf(HorsLigneError)
    expect(fetchMenus).not.toHaveBeenCalled()
    vi.restoreAllMocks()
  })
})
