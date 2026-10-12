import { describe, expect, it } from 'vitest'
import { shuffleArray } from '@/utils/aleatoir'
import { capitalize, slugify } from '@/utils/stringsUtlis'
import { orderMenuTitle } from '@/utils/arrayUtills'

describe('shuffleArray', () => {
  it('renvoie une permutation sans modifier le tableau d\'origine', () => {
    const source = [1, 2, 3, 4, 5]

    const shuffled = shuffleArray(source)

    expect(source).toEqual([1, 2, 3, 4, 5])
    expect([...shuffled].sort()).toEqual(source)
    expect(shuffled).not.toBe(source)
  })
})

describe('chaînes', () => {
  it('met une majuscule à la première lettre', () => {
    expect(capitalize('flexbox')).toBe('Flexbox')
    expect(capitalize('')).toBe('')
  })

  it('produit un slug sans accents ni caractères spéciaux', () => {
    expect(slugify('  Les Sélecteurs CSS !  ')).toBe('les-selecteurs-css')
  })
})

describe('orderMenuTitle', () => {
  it('trie les titres selon leur numéro de tête', () => {
    const menus = [{ title: '10. Fin' }, { title: '2. Suite' }, { title: '1. Début' }]

    expect(orderMenuTitle(menus).map((m) => m.title)).toEqual(['1. Début', '2. Suite', '10. Fin'])
    expect(orderMenuTitle(menus, false)[0].title).toBe('10. Fin')
  })

  it('renvoie un tableau vide si l\'entrée n\'est pas un tableau', () => {
    expect(orderMenuTitle(null)).toEqual([])
  })
})
