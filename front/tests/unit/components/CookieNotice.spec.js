import { afterEach, describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import CookieNotice from '@/components/layout/CookieNotice.vue'

const KEY = 'mediateur:cookieNotice:seen'

describe('information sur les cookies', () => {
  afterEach(() => localStorage.clear())

  it('informe sans rien demander, avec un lien vers la page de confidentialité', () => {
    const wrapper = mount(CookieNotice)
    expect(wrapper.text()).toContain("n'utilise que des cookies nécessaires")
    expect(wrapper.get('a').attributes('href')).toBe('/privacy#cookies')
    // Pas de choix « Accepter / Refuser » : il n'y a rien à refuser
    expect(wrapper.findAll('button').map((b) => b.text())).toEqual(["J'ai compris"])
  })

  it("disparaît après « J'ai compris » et ne revient pas à la visite suivante", async () => {
    const wrapper = mount(CookieNotice)
    await wrapper.get('button').trigger('click')
    expect(wrapper.find('section').exists()).toBe(false)
    expect(localStorage.getItem(KEY)).toBe('1')

    expect(mount(CookieNotice).find('section').exists()).toBe(false)
  })
})
