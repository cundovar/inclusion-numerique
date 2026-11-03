import api from '@/services/api'
import { createOfflineDb } from '@/utils/offlineDb'

// Ouverte au premier usage : un import seul n'ouvre pas IndexedDB
let offlineDb = null
const db = () => (offlineDb ??= createOfflineDb())

// Captures publiées demandées (marqueurs [capture:ID] d'un cours, diapositives « capture » d'un diaporama).
// Chaque capture reçue est gardée sur l'appareil ; sans réseau, on relit ces copies.
export async function chargerCaptures(ids) {
  const query = ids.map((id) => `id[]=${encodeURIComponent(id)}`).join('&')
  try {
    const { data } = await api.get(`/captures?${query}`)
    const captures = data.member ?? []
    await db().saveAll('captures', captures).catch(() => {})
    return captures
  } catch (error) {
    if (error.response) throw error
    return db().loadCaptures(ids)
  }
}
