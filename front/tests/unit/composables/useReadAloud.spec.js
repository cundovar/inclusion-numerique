import { describe, expect, it } from 'vitest'
import { extractReadableChunks, splitSentences } from '@/composables/ui/useReadAloud'

const build = (html) => {
  const root = document.createElement('div')
  root.innerHTML = html
  return root
}

describe('lecture à voix haute : extraction du texte', () => {
  it('lit les titres, paragraphes et listes dans l\'ordre du cours', () => {
    const root = build('<h2>Écrire un courriel</h2><p>Ouvrez votre messagerie.</p><ul><li>Cliquez sur Nouveau</li></ul>')

    expect(extractReadableChunks(root)).toEqual([
      'Écrire un courriel',
      'Ouvrez votre messagerie.',
      'Cliquez sur Nouveau'
    ])
  })

  it('ignore le code, les éléments décoratifs et ceux marqués à ne pas lire', () => {
    const root = build(
      '<p>Texte lu.</p><pre><code>console.log(1)</code></pre><p aria-hidden="true">Décor</p><p data-no-read>Interne</p>'
    )

    expect(extractReadableChunks(root)).toEqual(['Texte lu.'])
  })

  it('ne lit pas deux fois un bloc imbriqué', () => {
    const root = build('<ul><li><p>Un seul passage</p></li></ul>')

    expect(extractReadableChunks(root)).toEqual(['Un seul passage'])
  })

  it('retire le code en ligne d\'un paragraphe sans casser la phrase', () => {
    const root = build('<p>Tapez <code>ctrl+C</code> pour copier.</p>')

    expect(extractReadableChunks(root)).toEqual(['Tapez pour copier.'])
  })

  it('renvoie une liste vide sans contenu', () => {
    expect(extractReadableChunks(null)).toEqual([])
    expect(extractReadableChunks(build('<div></div>'))).toEqual([])
  })
})

describe('lecture à voix haute : découpage', () => {
  it('découpe un long texte par phrases sans dépasser la taille limite', () => {
    const sentence = 'Voici une phrase assez longue pour remplir le passage lu à voix haute. '
    const chunks = splitSentences(sentence.repeat(10))

    expect(chunks.length).toBeGreaterThan(1)
    expect(chunks.every((chunk) => chunk.length <= 260)).toBe(true)
    expect(chunks.join(' ').replace(/\s+/g, ' ')).toBe(sentence.repeat(10).trim().replace(/\s+/g, ' '))
  })

  it('garde un texte court en un seul passage', () => {
    expect(splitSentences('Bonjour tout le monde.')).toEqual(['Bonjour tout le monde.'])
  })
})
