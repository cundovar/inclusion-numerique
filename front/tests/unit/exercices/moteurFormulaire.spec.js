import { describe, expect, it } from 'vitest'
import { normaliser, numeroRecopie, recapitulatif, valeursDepart, verifierChamp, verifierFormulaire } from '@/exercices/moteur/moteurFormulaire'
import exercice from '@/exercices/remplir-un-formulaire'

const { formulaire, confirmation } = exercice.scenes[0]
const champ = (id) => formulaire.champs.find((item) => item.id === id)

describe('moteur du formulaire', () => {
  it('ignore majuscules, accents et espaces en trop', () => {
    expect(normaliser('  Dupré   ')).toBe('dupre')
    expect(verifierChamp(champ('nom'), 'dupre')).toBeNull()
    expect(verifierChamp(champ('nom'), 'Durand')).toBe('Recopiez le nom de la fiche : DUPRÉ.')
  })

  it('part d\'une date mal écrite, à corriger au bon format', () => {
    const depart = valeursDepart(formulaire)
    expect(depart.naissance).toBe('14-2-56')
    expect(verifierChamp(champ('naissance'), depart.naissance)).toContain('JJ/MM/AAAA')
    expect(verifierChamp(champ('naissance'), '15/02/1956')).toContain('14/02/1956')
    expect(verifierChamp(champ('naissance'), '14/02/1956')).toBeNull()
  })

  it('accepte un champ facultatif vide et exige un champ obligatoire', () => {
    expect(verifierChamp(champ('telephone'), '')).toBeNull()
    expect(verifierChamp(champ('ville'), '   ')).toBe('Indiquez la ville.')
    expect(verifierChamp(champ('code-postal'), '99 100')).toContain('cinq chiffres')
    expect(verifierChamp(champ('certification'), false)).toContain('Cochez la case')
  })

  it('liste les erreurs dans l\'ordre des champs', () => {
    const erreurs = verifierFormulaire(formulaire, valeursDepart(formulaire))
    expect(erreurs.map((erreur) => erreur.id)).toEqual(['nom', 'prenom', 'naissance', 'adresse', 'code-postal', 'ville', 'logement', 'certification'])
  })

  it('récapitule les champs saisis, sans la case à cocher', () => {
    const lignes = recapitulatif(formulaire, { ...valeursDepart(formulaire), nom: ' DUPRÉ ' })
    expect(lignes.find((ligne) => ligne.id === 'nom').valeur).toBe('DUPRÉ')
    expect(lignes.find((ligne) => ligne.id === 'telephone').valeur).toBe('Non renseigné')
    expect(lignes.some((ligne) => ligne.id === 'certification')).toBe(false)
  })

  it('reconnaît le numéro recopié, même sans tirets ni majuscules', () => {
    expect(numeroRecopie(confirmation, 'dem 2026 0417')).toBe(true)
    expect(numeroRecopie(confirmation, 'DEM-2026-0471')).toBe(false)
  })
})
