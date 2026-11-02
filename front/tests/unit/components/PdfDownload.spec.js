import { afterEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import PdfDownload from '@/components/features/pages/PdfDownload.vue'

describe('téléchargement en PDF', () => {
  afterEach(() => vi.restoreAllMocks())

  it('propose le PDF du serveur en téléchargement', () => {
    const wrapper = mount(PdfDownload, { props: { href: '/cours/utiliser-la-souris.pdf' } })
    const link = wrapper.get('a')
    expect(link.attributes('href')).toBe('/cours/utiliser-la-souris.pdf')
    expect(link.attributes('download')).toBeDefined()
    expect(link.text()).toBe('Télécharger en PDF')
  })

  it('dit hors ligne que le PDF demande une connexion, puis le repropose au retour du réseau', async () => {
    const onLine = vi.spyOn(window.navigator, 'onLine', 'get').mockReturnValue(false)
    const wrapper = mount(PdfDownload, { props: { href: '/module/3.pdf', label: "Fiche d'atelier en PDF" } })
    expect(wrapper.find('a').exists()).toBe(false)
    expect(wrapper.get('[role="status"]').text()).toContain('disponible avec une connexion')

    onLine.mockReturnValue(true)
    window.dispatchEvent(new Event('online'))
    await wrapper.vm.$nextTick()
    expect(wrapper.get('a').attributes('href')).toBe('/module/3.pdf')
  })
})
