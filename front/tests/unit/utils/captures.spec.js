import { describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { expandCaptures, findCaptureIds } from '@/utils/captures'
import SafeHtml from '@/components/ui/SafeHtml.vue'

const caf = {
  id: 12,
  kind: 'capture',
  service: 'CAF',
  sourceUrl: 'https://www.caf.fr/',
  capturedAt: '2026-10-15',
  altText: "Page d'accueil de caf.fr, bouton « Mon Compte » en haut à droite",
  caption: 'Le bouton « Mon Compte » ouvre la connexion.',
  url: '/uploads/course-media/abc.png',
  width: 1280,
  height: 800,
}

const parse = (html) => new DOMParser().parseFromString(html, 'text/html').body

describe('marqueurs de capture', () => {
  it('liste chaque capture une seule fois', () => {
    expect(findCaptureIds('<p>[capture:12]</p><p>voir [capture:3] et [capture:12]</p>')).toEqual([12, 3])
    expect(findCaptureIds(null)).toEqual([])
  })

  it('remplace un marqueur seul par la figure complète, sans paragraphe vide', async () => {
    const fetchCaptures = vi.fn(async () => [caf])
    const body = parse(await expandCaptures('<p>Avant</p><p> [capture:12] </p><p>Après</p>', fetchCaptures))

    expect(fetchCaptures).toHaveBeenCalledWith([12])
    expect(body.children).toHaveLength(3)
    const figure = body.querySelector('figure.capture-figure')
    expect(figure.querySelector('img').getAttribute('src')).toBe('/uploads/course-media/abc.png')
    expect(figure.querySelector('img').getAttribute('alt')).toBe(caf.altText)
    expect(figure.querySelector('.capture-caption').textContent).toBe(caf.caption)
    expect(figure.querySelector('.capture-source').textContent).toBe('Capture du site CAF, le 15/10/2026. Voir la page officielle')
    expect(figure.querySelector('.capture-source a').getAttribute('href')).toBe('https://www.caf.fr/')
    // RGAA 1.9
    expect(figure.getAttribute('role')).toBe('figure')
    expect(figure.getAttribute('aria-label')).toBe(caf.caption)
  })

  it('garde la phrase entière et place la capture juste après', async () => {
    const body = parse(await expandCaptures('<p>Regardez <strong>bien</strong> [capture:12] ici.</p><p>Suite</p>', async () => [caf]))

    expect(body.children[0].textContent).toBe('Regardez bien  ici.')
    expect(body.children[1].tagName).toBe('FIGURE')
    expect(body.children[2].textContent).toBe('Suite')
  })

  it('sort aussi la capture d\'un texte en gras dans un paragraphe', async () => {
    const body = parse(await expandCaptures('<p><strong>[capture:12]</strong></p>', async () => [caf]))

    expect(body.children).toHaveLength(1)
    expect(body.children[0].tagName).toBe('FIGURE')
  })

  it('insère la capture à sa place dans une liste', async () => {
    const body = parse(await expandCaptures('<ol><li>Ouvrez le site [capture:12]</li></ol>', async () => [caf]))

    expect(body.querySelector('li figure')).not.toBeNull()
    expect(body.querySelector('li').textContent).toContain('Ouvrez le site')
  })

  it('signale une illustration comme fictive', async () => {
    const illustration = { ...caf, kind: 'illustration', sourceUrl: null, capturedAt: null }
    const body = parse(await expandCaptures('<p>[capture:12]</p>', async () => [illustration]))

    expect(body.querySelector('.capture-source').textContent).toBe("Illustration pédagogique : ce n'est pas l'écran réel du service.")
  })

  it('traite les textes de la capture comme du texte, jamais comme du HTML', async () => {
    const piege = { ...caf, caption: '<img src=x onerror=alert(1)>', altText: '"><script>alert(1)</script>' }
    const body = parse(await expandCaptures('<p>[capture:12]</p>', async () => [piege]))

    expect(body.querySelectorAll('img')).toHaveLength(1)
    expect(body.querySelector('script')).toBeNull()
    expect(body.querySelector('.capture-caption').textContent).toBe('<img src=x onerror=alert(1)>')
  })

  it('retire le marqueur d\'une capture introuvable ou si le réseau manque', async () => {
    expect(parse(await expandCaptures('<p>Texte</p><p>[capture:99]</p>', async () => [])).innerHTML).toBe('<p>Texte</p>')
    const offline = await expandCaptures('<p>Texte</p><p>[capture:12]</p>', async () => { throw new Error('hors ligne') })
    expect(parse(offline).innerHTML).toBe('<p>Texte</p>')
  })

  it('ne fait aucun appel quand le cours n\'a pas de capture', async () => {
    const fetchCaptures = vi.fn()
    expect(await expandCaptures('<p>Texte</p>', fetchCaptures)).toBe('<p>Texte</p>')
    expect(fetchCaptures).not.toHaveBeenCalled()
  })
})

describe('affichage sécurisé des images', () => {
  it('garde la figure d\'une capture et retire toute autre image', async () => {
    const html = await expandCaptures('<p>[capture:12]</p><img src="https://traceur.example/pixel.gif" alt=""><img src="/uploads/course-media/../secret.png" alt="">', async () => [caf])
    const wrapper = mount(SafeHtml, { props: { html } })

    const images = wrapper.findAll('img')
    expect(images).toHaveLength(1)
    expect(images[0].attributes('src')).toBe('/uploads/course-media/abc.png')
    expect(wrapper.find('figcaption').exists()).toBe(true)
    expect(wrapper.find('figure').attributes('aria-label')).toBe(caf.caption)
    expect(wrapper.find('.capture-source a').attributes('rel')).toBe('noopener noreferrer')
  })
})
