import { test as base, expect } from '@playwright/test'

// Visiteur ayant déjà vu la notification des mises à jour (cookie `notifVue`, voir useNotifModal.js) :
// la fenêtre modale de première visite ne masque pas la page.
export const test = base.extend({
  context: async ({ context, baseURL }, use) => {
    await context.addCookies([{ name: 'notifVue', value: 'true', url: baseURL }])
    await use(context)
  }
})

export { expect }
