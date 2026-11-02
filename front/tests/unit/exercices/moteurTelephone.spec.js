import { describe, expect, it } from 'vitest'
import { appliquer, conditionRemplie, creerEtat, defilementMax } from '@/exercices/moteur/moteurTelephone'
import exercice from '@/exercices/gestes-du-telephone'

const scene = exercice.scenes[0]
const { telephone } = scene

// Applique une suite d'événements depuis l'état de départ
const jouer = (...evenements) => evenements.reduce((etat, evenement) => appliquer(scene, etat, evenement), creerEtat(scene))

const toucher = (cible) => ({ type: 'toucher', cible })
const glisser = (direction = 'bas') => ({ type: 'glisser', cible: 'liste-messages', direction })
const regler = (valeur) => ({ type: 'regler', cible: 'volume', valeur })

const liste = telephone.ecrans.messages.zones[0]

describe('moteur du téléphone', () => {
  it('démarre sur l\'accueil avec les valeurs prévues, à la première mission', () => {
    const etat = creerEtat(scene)

    expect(etat).toMatchObject({ ecran: 'accueil', valeurs: { volume: 3, wifi: false }, etape: 0, termine: false })
  })

  it('ne modifie jamais l\'état reçu', () => {
    const depart = creerEtat(scene)
    const copie = structuredClone(depart)

    appliquer(scene, depart, toucher('app-reglages'))
    appliquer(scene, depart, regler(9))

    expect(depart).toEqual(copie)
  })

  it('ouvre une application au toucher et réussit la première mission', () => {
    const etat = jouer(toucher('app-messages'))

    expect(etat.ecran).toBe('messages')
    expect(etat.etape).toBe(1)
    expect(etat.reussites).toEqual(['Bravo, l\'application « Messages » est ouverte.'])
    expect(etat.annonce).toBe('Application « Messages » ouverte.')
  })

  it('ignore une zone qui n\'est pas sur l\'écran courant', () => {
    const etat = jouer(toucher('wifi'))

    expect(etat.valeurs.wifi).toBe(false)
    expect(etat.ecran).toBe('accueil')
    expect(etat.annonce).toBe('')
  })

  it('fait défiler la liste cran par cran, sans dépasser les bords', () => {
    const max = defilementMax(liste)
    expect(max).toBe(liste.elements.length - liste.visibles)

    let etat = jouer(toucher('app-messages'), glisser('haut'))
    expect(etat.defilement['liste-messages'] ?? 0).toBe(0)
    expect(etat.annonce).toBe('Vous êtes en haut de la liste.')

    etat = jouer(toucher('app-messages'), glisser())
    expect(etat.defilement['liste-messages']).toBe(1)
    expect(etat.annonce).toBe('Messages 2 à 5 sur 8.')
    expect(etat.etape).toBe(1)

    const fin = jouer(toucher('app-messages'), ...Array(max + 2).fill(glisser()))
    expect(fin.defilement['liste-messages']).toBe(max)
    expect(fin.annonce).toBe('Vous êtes à la fin de la liste.')
    expect(fin.etape).toBe(2)
  })

  it('borne le curseur et réussit la mission du volume à partir de 8', () => {
    const base = [toucher('app-messages'), ...Array(defilementMax(liste)).fill(glisser()), toucher('accueil'), toucher('app-reglages')]

    expect(jouer(...base, regler(7)).etape).toBe(2)
    const etat = jouer(...base, regler(8))
    expect(etat.etape).toBe(3)
    expect(etat.annonce).toBe('Volume : 8 sur 10.')
    expect(jouer(...base, regler(99)).valeurs.volume).toBe(10)
    expect(jouer(...base, regler(-4)).valeurs.volume).toBe(0)
  })

  it('bascule l\'interrupteur du Wi-Fi', () => {
    const etat = jouer(toucher('app-reglages'), toucher('wifi'))
    expect(etat.valeurs.wifi).toBe(true)
    expect(etat.annonce).toBe('Wi-Fi activé.')
    expect(appliquer(scene, etat, toucher('wifi')).annonce).toBe('Wi-Fi désactivé.')
  })

  it('revient à l\'écran parent avec « retour » et à l\'accueil depuis partout', () => {
    expect(jouer(toucher('app-photos'), toucher('retour')).ecran).toBe('accueil')
    expect(jouer(toucher('app-reglages'), toucher('accueil')).ecran).toBe('accueil')
    // L'accueil n'a pas de parent : « retour » n'y fait rien
    expect(jouer(toucher('retour')).ecran).toBe('accueil')
  })

  it('termine après les cinq missions faites dans l\'ordre', () => {
    const etat = jouer(
      toucher('app-messages'),
      ...Array(defilementMax(liste)).fill(glisser()),
      toucher('accueil'),
      toucher('app-reglages'),
      regler(9),
      toucher('wifi'),
      toucher('accueil'),
    )

    expect(etat.termine).toBe(true)
    expect(etat.etape).toBe(scene.missions.length)
    expect(etat.reussites).toEqual(['Bravo, voici de nouveau l\'écran d\'accueil.'])
    // Une fois terminé, plus rien ne change
    expect(appliquer(scene, etat, toucher('app-photos'))).toBe(etat)
  })

  it('valide d\'un coup les missions déjà remplies en avance', () => {
    const etat = jouer(
      toucher('app-reglages'),
      regler(10),
      toucher('wifi'),
      toucher('accueil'),
      toucher('app-messages'),
      ...Array(defilementMax(liste)).fill(glisser()),
    )

    // Volume et Wi-Fi sont déjà réglés : la liste finie mène directement à la dernière mission
    expect(etat.etape).toBe(4)
    expect(etat.reussites).toEqual([
      'Bravo, vous avez vu toute la liste.',
      'Bravo, le volume est monté.',
      'Bravo, le Wi-Fi est activé.',
    ])
  })

  it('évalue chaque type de condition', () => {
    const etat = { ...creerEtat(scene), ecran: 'reglages', valeurs: { volume: 8, wifi: true } }

    expect(conditionRemplie(telephone, etat, { ecran: 'reglages' })).toBe(true)
    expect(conditionRemplie(telephone, etat, { ecran: 'accueil' })).toBe(false)
    expect(conditionRemplie(telephone, etat, { valeur: 'volume', min: 9 })).toBe(false)
    expect(conditionRemplie(telephone, etat, { valeur: 'wifi', egal: true })).toBe(true)
    expect(conditionRemplie(telephone, etat, { defilementFin: 'liste-messages' })).toBe(false)
  })
})
