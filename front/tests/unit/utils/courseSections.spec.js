import { describe, expect, it } from 'vitest'
import { sectionKind, structureCourse } from '@/utils/courseSections'

const parse = (html) => new DOMParser().parseFromString(html, 'text/html').body

describe('parties d\'un cours', () => {
  it('reconnaît les intitulés des gabarits, sans tenir compte des accents ni des majuscules', () => {
    expect(sectionKind('Étape 2 : Ouvrir la messagerie')).toBe('etape')
    expect(sectionKind('3. Faire faire')).toBe('etape')
    expect(sectionKind('ATTENTION')).toBe('attention')
    expect(sectionKind('Points de vigilance')).toBe('attention')
    expect(sectionKind('À vous')).toBe('exercice')
    expect(sectionKind('Si ça ne marche pas')).toBe('aide')
    expect(sectionKind('Quand orienter')).toBe('aide')
    expect(sectionKind("Ce qu'il vous faut")).toBe('repere')
    expect(sectionKind('Ce qu’il vous faut')).toBe('repere')
    expect(sectionKind('Avant de commencer')).toBe('repere')
    expect(sectionKind('Pourquoi sauvegarder')).toBe('simple')
  })

  it('range chaque titre et son texte dans une partie, sans toucher à ce qui précède', () => {
    const body = parse(structureCourse(
      '<p>Intro</p><h2>Avant de commencer</h2><ul><li>Un ordinateur</li></ul><h2>Attention</h2><p>Mot de passe</p><figure class="capture-figure"></figure>',
    ))

    expect(body.firstElementChild.outerHTML).toBe('<p>Intro</p>')
    const sections = body.querySelectorAll('.cours-section')
    expect([...sections].map((s) => s.className)).toEqual([
      'cours-section cours-section--repere',
      'cours-section cours-section--attention',
    ])
    expect(sections[0].querySelector('li').textContent).toBe('Un ordinateur')
    expect(sections[1].querySelector('.capture-figure')).not.toBeNull()
  })

  it('numérote les étapes et rappelle leur nombre', () => {
    const body = parse(structureCourse('<h2>Étape 1 : Allumer</h2><p>a</p><h2>Étape 2 : Cliquer</h2><p>b</p>'))
    const h2 = body.querySelectorAll('h2')

    expect(h2[1].getAttribute('data-etape')).toBe('2')
    expect(h2[1].querySelector('.cours-etape__label').textContent).toBe('Étape 2 sur 2')
    expect(h2[1].querySelector('.cours-etape__titre').textContent).toBe('Cliquer')
    // Le titre complet reste lisible par un lecteur d'écran et par le sommaire
    expect(h2[1].textContent).toBe('Étape 2 sur 2 Cliquer')
  })

  it('garde le titre des parties numérotées du gabarit « accompagner »', () => {
    const h2 = parse(structureCourse('<h2>2. Montrer</h2><p>x</p>')).querySelector('h2')

    expect(h2.getAttribute('data-etape')).toBe('2')
    expect(h2.textContent).toBe('2. Montrer')
  })

  it('laisse un cours sans titre tel quel', () => {
    expect(structureCourse('<p>Texte</p>')).toBe('<p>Texte</p>')
    expect(structureCourse(null)).toBe('')
  })
})
