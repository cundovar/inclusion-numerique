import { describe, expect, it } from 'vitest'
import { AUDIENCES } from '@/config/audiences'
import { listerExercices, trouverExercice } from '@/exercices'
import { verifierFormulaire } from '@/exercices/moteur/moteurFormulaire'

const exercices = listerExercices()

// Champs qui peuvent rester vides : un résultat sans étiquette n'est pas une annonce,
// et un exercice peut ne renvoyer vers aucun cours
const VIDES_AUTORISES = new Set(['etiquette', 'coursSlug'])

const champsVides = (valeur, chemin = '') => {
  const cle = chemin.split('.').pop()
  if (VIDES_AUTORISES.has(cle)) return []
  if (valeur === null || valeur === undefined) return [chemin]
  if (typeof valeur === 'string') return valeur.trim() === '' ? [chemin] : []
  if (Array.isArray(valeur)) {
    if (valeur.length === 0) return [chemin]
    return valeur.flatMap((item, index) => champsVides(item, `${chemin}.${index}`))
  }
  if (typeof valeur === 'object') {
    return Object.entries(valeur).flatMap(([nom, item]) => champsVides(item, chemin ? `${chemin}.${nom}` : nom))
  }
  return []
}

const TYPES_CONNUS = ['choisir-resultat', 'telephone', 'formulaire']

const scenesDeType = (exercice, type) => exercice.scenes.filter((scene) => scene.type === type)

// Textes lus par la personne pour chaque type de scène (les adresses sont contrôlées à part)
const TEXTES_PAR_TYPE = {
  'choisir-resultat': (scene) => scene.resultats.flatMap((resultat) => [
    resultat.etiquette,
    resultat.titre,
    resultat.extrait,
    resultat.retour,
  ]),
  telephone: (scene) => [
    ...Object.values(scene.telephone.ecrans).flatMap((ecran) => [
      ecran.titre,
      ...ecran.zones.flatMap((zone) => [
        zone.libelle,
        zone.texte,
        zone.refus,
        zone.avec,
        ...(zone.recus ?? []),
        ...(zone.elements ?? []).flatMap((element) => [element.de, element.texte]),
      ]),
    ]),
    ...scene.missions.flatMap((mission) => [mission.consigne, mission.aide, mission.reussite]),
  ],
  formulaire: (scene) => [
    scene.navigateur.onglet,
    scene.fiche.titre,
    ...scene.fiche.lignes.flatMap((ligne) => [ligne.libelle, ligne.valeur]),
    scene.formulaire.titre,
    scene.formulaire.introduction,
    scene.formulaire.bouton,
    ...scene.formulaire.champs.flatMap((champ) => [champ.libelle, champ.indication, ...(champ.options ?? []), ...Object.values(champ.erreurs)]),
    ...Object.values(scene.recapitulatif),
    scene.confirmation.titre,
    scene.confirmation.texte,
    scene.confirmation.question,
    scene.confirmation.erreur,
  ],
}

const textesAffiches = (exercice) => [
  exercice.titre,
  exercice.module,
  exercice.duree,
  exercice.consigne,
  ...exercice.scenes.flatMap((scene) => TEXTES_PAR_TYPE[scene.type](scene)),
  exercice.conclusion.reussite,
  ...exercice.conclusion.aRetenir,
].filter(Boolean)

const phrases = (texte) => texte.split(/[.!?…](?=\s|$)/).map((phrase) => phrase.trim()).filter(Boolean)
const nombreDeMots = (phrase) => phrase.split(/\s+/).filter((mot) => /[\p{L}\p{N}]/u.test(mot)).length

const adresseFictive = (adresse) => {
  const { hostname } = new URL(adresse)
  return hostname.endsWith('.example') || hostname === 'example.com' || hostname.endsWith('.example.com')
}

describe('données des exercices', () => {
  it('trouve au moins un exercice', () => {
    expect(exercices.length).toBeGreaterThan(0)
  })

  it('donne un slug unique à chaque exercice', () => {
    const slugs = exercices.map((exercice) => exercice.slug)
    expect(new Set(slugs).size).toBe(slugs.length)
  })

  it('retrouve un exercice par son slug et renvoie null sinon', () => {
    expect(trouverExercice('trouvez-le-vrai-site')?.titre).toBe('Trouvez le vrai site')
    expect(trouverExercice('inconnu')).toBeNull()
  })

  describe.each(exercices.map((exercice) => [exercice.slug, exercice]))('%s', (_slug, exercice) => {
    it('vise un public existant', () => {
      expect(AUDIENCES.map((audience) => audience.key)).toContain(exercice.public)
    })

    it('n\'a aucun champ vide', () => {
      expect(champsVides(exercice)).toEqual([])
    })

    it('n\'utilise que des types de scènes connus', () => {
      for (const scene of exercice.scenes) {
        expect(TYPES_CONNUS).toContain(scene.type)
      }
    })

    it('a au moins un bon résultat et des identifiants uniques par scène', () => {
      for (const scene of scenesDeType(exercice, 'choisir-resultat')) {
        expect(scene.resultats.some((resultat) => resultat.correct === true)).toBe(true)
        const ids = scene.resultats.map((resultat) => resultat.id)
        expect(new Set(ids).size).toBe(ids.length)
      }
    })

    it('n\'utilise que des adresses fictives', () => {
      const adresses = [
        ...scenesDeType(exercice, 'choisir-resultat').flatMap((scene) => scene.resultats.map((resultat) => resultat.adresse)),
        ...scenesDeType(exercice, 'formulaire').map((scene) => scene.navigateur.adresse),
      ]
      expect(adresses.filter((adresse) => !adresseFictive(adresse))).toEqual([])
    })

    it('relie chaque téléphone à des écrans, zones et missions existants', () => {
      for (const { telephone, missions } of scenesDeType(exercice, 'telephone')) {
        const ecrans = Object.keys(telephone.ecrans)
        const zones = Object.values(telephone.ecrans).flatMap((ecran) => ecran.zones)
        const ids = zones.map((zone) => zone.id)

        expect(ecrans).toContain(telephone.ecranDepart)
        expect(ecrans).toContain('accueil')
        expect(new Set(ids).size).toBe(ids.length)
        expect(missions.length).toBeGreaterThan(0)
        expect(new Set(missions.map((mission) => mission.id)).size).toBe(missions.length)

        for (const ecran of Object.values(telephone.ecrans)) {
          if (ecran.parent) expect(ecrans).toContain(ecran.parent)
        }
        for (const zone of zones) {
          if (zone.aller) expect(ecrans).toContain(zone.aller)
          for (const cle of [zone.valeur, zone.ouvre, zone.ouvert].filter(Boolean)) {
            expect(Object.keys(telephone.valeursDepart)).toContain(cle)
          }
        }
        for (const { condition } of missions) {
          if (condition.ecran) expect(ecrans).toContain(condition.ecran)
          if (condition.defilementFin) expect(ids).toContain(condition.defilementFin)
          if (condition.valeur) expect(Object.keys(telephone.valeursDepart)).toContain(condition.valeur)
        }
      }
    })

    it('donne une fiche qui remplit son formulaire sans erreur', () => {
      for (const { formulaire } of scenesDeType(exercice, 'formulaire')) {
        const ids = formulaire.champs.map((champ) => champ.id)
        expect(new Set(ids).size).toBe(ids.length)
        // Valeurs attendues (ou vide pour un champ libre) : aucune erreur
        const valeurs = Object.fromEntries(formulaire.champs.map((champ) => [
          champ.id,
          champ.type === 'case' ? true : (champ.attendu ?? ''),
        ]))
        expect(verifierFormulaire(formulaire, valeurs)).toEqual([])
        for (const champ of formulaire.champs) {
          if (champ.type === 'choix') expect(champ.options).toContain(champ.attendu)
          if (champ.format && champ.attendu) expect(new RegExp(champ.format, 'u').test(champ.attendu)).toBe(true)
        }
      }
    })

    it('n\'a aucune phrase de plus de 25 mots', () => {
      const tropLongues = textesAffiches(exercice)
        .flatMap(phrases)
        .filter((phrase) => nombreDeMots(phrase) > 25)
      expect(tropLongues).toEqual([])
    })
  })
})
