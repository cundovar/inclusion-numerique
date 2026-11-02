import { describe, expect, it } from 'vitest'
import { appliquer, creerEtat, majusculeAuto } from '@/exercices/moteur/moteurTelephone'
import exercice from '@/exercices/envoyer-un-message'

const scene = exercice.scenes[0]

const jouer = (...evenements) => evenements.reduce((etat, evenement) => appliquer(scene, etat, evenement), creerEtat(scene))

const toucher = (cible) => ({ type: 'toucher', cible })
const taper = (touche) => ({ type: 'taper', cible: 'clavier', touche })
const ouvrir = toucher('champ-message')
const effacer = taper('effacer')

describe('moteur du téléphone : écrire et envoyer un message', () => {
  it('démarre sur la conversation avec le brouillon fautif et le clavier fermé', () => {
    expect(creerEtat(scene)).toMatchObject({
      ecran: 'conversation',
      valeurs: { brouillon: 'Oui, avec plaisur', clavierOuvert: false },
      envoyes: [],
      etape: 0,
    })
  })

  it('n\'écrit rien tant que le clavier est fermé', () => {
    const etat = jouer(taper('x'))
    expect(etat.valeurs.brouillon).toBe('Oui, avec plaisur')
  })

  it('ouvre le clavier en touchant la zone de texte', () => {
    const etat = jouer(ouvrir)
    expect(etat.valeurs.clavierOuvert).toBe(true)
    expect(etat.annonce).toBe('Clavier ouvert.')
    expect(etat.etape).toBe(1)
  })

  it('efface la dernière lettre et annonce laquelle', () => {
    const etat = jouer(ouvrir, effacer)
    expect(etat.valeurs.brouillon).toBe('Oui, avec plaisu')
    expect(etat.annonce).toBe('« r » effacé.')
    // « plaisu » contient encore la faute
    expect(etat.etape).toBe(1)
  })

  it('valide la correction dès que la faute est effacée, même si on efface trop', () => {
    expect(jouer(ouvrir, effacer, effacer).etape).toBe(2)
    const trop = jouer(ouvrir, effacer, effacer, effacer, effacer)
    expect(trop.valeurs.brouillon).toBe('Oui, avec pla')
    expect(trop.etape).toBe(2)
  })

  it('écrit les lettres et réussit la mission quand le mot est juste', () => {
    const etat = jouer(ouvrir, effacer, effacer, taper('i'), taper('r'))
    expect(etat.valeurs.brouillon).toBe('Oui, avec plaisir')
    expect(etat.annonce).toBe('« r »')
    expect(etat.etape).toBe(3)
  })

  it('met une majuscule automatique au début du message et après un point', () => {
    expect(majusculeAuto('')).toBe(true)
    expect(majusculeAuto('Merci. ')).toBe(true)
    expect(majusculeAuto('Merci')).toBe(false)

    const vide = jouer(ouvrir, ...Array(20).fill(effacer))
    expect(vide.valeurs.brouillon).toBe('')
    expect(vide.conseil).toBe('Le message est vide.')
    const etat = appliquer(scene, appliquer(scene, vide, taper('o')), taper('k'))
    expect(etat.valeurs.brouillon).toBe('Ok')
  })

  it('tape une espace et la virgule', () => {
    const etat = jouer(ouvrir, taper('espace'), taper(','))
    expect(etat.valeurs.brouillon).toBe('Oui, avec plaisur ,')
  })

  it('refuse d\'envoyer un message encore faux, avec un conseil visible', () => {
    const etat = jouer(ouvrir, effacer, effacer, toucher('envoyer'))
    expect(etat.envoyes).toEqual([])
    expect(etat.valeurs.brouillon).toBe('Oui, avec plais')
    expect(etat.conseil).toBe('Corrigez d\'abord le message. Il doit afficher « Oui, avec plaisir ».')
    // Le conseil disparaît à l'action suivante
    expect(appliquer(scene, etat, taper('i')).conseil).toBe('')
  })

  it('envoie le message corrigé, vide la zone de texte et termine l\'exercice', () => {
    const etat = jouer(ouvrir, effacer, effacer, taper('i'), taper('r'), toucher('envoyer'))
    expect(etat.envoyes).toEqual(['Oui, avec plaisir'])
    expect(etat.valeurs.brouillon).toBe('')
    expect(etat.annonce).toBe('Message envoyé : « Oui, avec plaisir ».')
    expect(etat.termine).toBe(true)
    expect(etat.reussites).toEqual(['Bravo, votre message est envoyé.'])
  })
})
