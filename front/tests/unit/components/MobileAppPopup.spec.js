import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import MobileAppPopup from '@/components/layout/MobileAppPopup.vue'

const monter = (props = {}) => mount(MobileAppPopup, { props, global: { stubs: { teleport: true } } })

describe("fenêtre d'installation de l'application", () => {
  it('propose « Installer » et laisse remettre à plus tard', async () => {
    const wrapper = monter()
    const [installer, plusTard] = wrapper.findAll('.install-popup__button')
    expect(installer.text()).toBe('Installer')
    expect(plusTard.text()).toBe('Plus tard')

    await installer.trigger('click')
    await plusTard.trigger('click')
    expect(wrapper.emitted('install')).toHaveLength(1)
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  it("affiche les explications dans un encadré quand l'installation directe n'est pas possible", () => {
    expect(monter().get('[data-testid="instructions-installation"]').classes()).not.toContain('install-popup__instructions--shown')

    const zone = monter({ instructions: 'Ouvrez le menu du navigateur.' }).get('[data-testid="instructions-installation"]')
    expect(zone.classes()).toContain('install-popup__instructions--shown')
    expect(zone.attributes('role')).toBe('status')
    expect(zone.text()).toBe('Ouvrez le menu du navigateur.')
  })
})
