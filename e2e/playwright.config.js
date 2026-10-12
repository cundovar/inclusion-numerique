import { defineConfig, devices } from '@playwright/test'

// Tests de bout en bout sur l'application locale (Docker), avec les données de la base de développement.
// Lancement : `make test-e2e` (service compose `e2e`, image officielle Playwright).
export default defineConfig({
  testDir: './tests',
  timeout: 60_000,
  expect: { timeout: 10_000 },
  fullyParallel: false,
  retries: 0,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    // localhost : contexte sécurisé, nécessaire au service worker
    baseURL: process.env.E2E_BASE_URL || 'http://localhost:8092',
    locale: 'fr-FR',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure'
  },
  projects: [
    { name: 'desktop-chromium', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } } }
  ]
})
