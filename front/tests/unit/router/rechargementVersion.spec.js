import { describe, expect, it, vi } from 'vitest'
import { estErreurDeChargement, rechargerUneFois } from '@/router/rechargementVersion'

const stockageVide = () => {
  const valeurs = new Map()
  return { getItem: (cle) => valeurs.get(cle) ?? null, setItem: (cle, valeur) => valeurs.set(cle, valeur) }
}

describe('rechargement après une mise en ligne', () => {
  it('reconnaît un fichier de page devenu introuvable', () => {
    expect(estErreurDeChargement(new TypeError('Failed to fetch dynamically imported module: https://site.example/build/assets/questionQCM-Cn6WAhmO.js'))).toBe(true)
    expect(estErreurDeChargement(new TypeError('error loading dynamically imported module'))).toBe(true)
    expect(estErreurDeChargement(new Error('Request failed with status code 404'))).toBe(false)
  })

  it('recharge une seule fois vers la page demandée', () => {
    const stockage = stockageVide()
    const adresse = { assign: vi.fn() }

    expect(rechargerUneFois('/spa/qcm/0', { stockage, adresse, maintenant: 1_000 })).toBe(true)
    expect(adresse.assign).toHaveBeenCalledWith('/spa/qcm/0')

    // Si la nouvelle version échoue aussi, on ne boucle pas
    expect(rechargerUneFois('/spa/qcm/0', { stockage, adresse, maintenant: 5_000 })).toBe(false)
    expect(adresse.assign).toHaveBeenCalledOnce()

    expect(rechargerUneFois('/spa/qcm/0', { stockage, adresse, maintenant: 20_000 })).toBe(true)
  })

  it('recharge même si le stockage du navigateur est bloqué', () => {
    const stockage = { getItem: () => { throw new Error('bloqué') }, setItem: () => { throw new Error('bloqué') } }
    const adresse = { assign: vi.fn() }

    expect(rechargerUneFois('/spa/qcm', { stockage, adresse, maintenant: 1_000 })).toBe(true)
  })
})
