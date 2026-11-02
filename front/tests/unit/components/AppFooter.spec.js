import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import AppFooter from '@/components/layout/AppFooter.vue'

describe('pied de page', () => {
  it('donne accès aux trois pages légales depuis toutes les pages', () => {
    const wrapper = mount(AppFooter)
    const links = wrapper.findAll('nav[aria-label="Informations légales"] a')
    expect(links.map((link) => [link.text(), link.attributes('href')])).toEqual([
      ['Mentions légales', '/mentions-legales'],
      ['Confidentialité et cookies', '/privacy'],
      ["Conditions d'utilisation", '/terms'],
    ])
    expect(wrapper.text()).toContain('site gratuit, sans publicité')
  })
})
