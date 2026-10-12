import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import SafeHtml from '@/components/ui/SafeHtml.vue'

// ENF-03 : le HTML venant de l'API est nettoyé avant affichage
const render = (html, props = {}) => mount(SafeHtml, { props: { html, ...props } })

describe('SafeHtml', () => {
  it('conserve le contenu de cours autorisé', () => {
    const wrapper = render('<h2 class="titre">Flexbox</h2><pre><code>display: flex;</code></pre>')

    expect(wrapper.find('h2.titre').text()).toBe('Flexbox')
    expect(wrapper.find('pre code').text()).toBe('display: flex;')
  })

  it('supprime les balises script et iframe', () => {
    const wrapper = render('<p>ok</p><script>alert(1)</script><iframe src="https://evil.test"></iframe>')

    expect(wrapper.html()).not.toContain('<script')
    expect(wrapper.html()).not.toContain('<iframe')
    expect(wrapper.text()).toBe('ok')
  })

  it('supprime les gestionnaires d\'événements et les styles en ligne', () => {
    const wrapper = render('<p onclick="alert(1)" style="color:red">texte</p>')
    const p = wrapper.find('p')

    expect(p.attributes('onclick')).toBeUndefined()
    expect(p.attributes('style')).toBeUndefined()
  })

  it('retire les liens javascript: et data:', () => {
    const wrapper = render('<a href="javascript:alert(1)">a</a><a href="data:text/html,x">b</a>')

    wrapper.findAll('a').forEach((a) => expect(a.attributes('href')).toBeUndefined())
  })

  it('protège les liens ouverts dans un nouvel onglet', () => {
    const wrapper = render('<a href="https://developer.mozilla.org" target="_blank">MDN</a>')

    expect(wrapper.find('a').attributes('rel')).toBe('noopener noreferrer')
  })

  it('rend la balise demandée et accepte un contenu vide', () => {
    const wrapper = render('', { tag: 'section' })

    // Le gabarit a des commentaires autour de <component> : la racine est un fragment
    const section = wrapper.find('section')
    expect(section.exists()).toBe(true)
    expect(section.element.innerHTML).toBe('')
    expect(wrapper.find('div').exists()).toBe(false)
  })
})
