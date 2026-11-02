import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

// Le module écoute beforeinstallprompt dès son chargement : on le recharge à chaque test
const charger = async () => {
  vi.resetModules()
  return import('@/composables/api/usePWA')
}

const evenementInstallation = (outcome = 'accepted') => {
  const evenement = new Event('beforeinstallprompt', { cancelable: true })
  evenement.prompt = vi.fn()
  evenement.userChoice = Promise.resolve({ outcome })
  return evenement
}

beforeEach(() => {
  localStorage.clear()
  vi.useFakeTimers()
})

afterEach(() => {
  vi.useRealTimers()
  vi.restoreAllMocks()
  vi.unstubAllGlobals()
})

describe('usePWA', () => {
  it('garde l\'événement d\'installation reçu avant l\'appel du composable', async () => {
    const { usePWA } = await charger()
    const evenement = evenementInstallation()
    window.dispatchEvent(evenement)

    const { installPWA, instructions } = usePWA()
    await installPWA()

    expect(evenement.defaultPrevented).toBe(true)
    expect(evenement.prompt).toHaveBeenCalledOnce()
    expect(instructions.value).toBe('')
  })

  it('ferme la fenêtre si l\'installation est acceptée', async () => {
    const { usePWA } = await charger()
    window.dispatchEvent(evenementInstallation('accepted'))
    const pwa = usePWA()
    pwa.showMobileAppPopup.value = true

    await pwa.installPWA()

    expect(pwa.showMobileAppPopup.value).toBe(false)
    expect(localStorage.getItem('mobileAppPopupShown')).toBe('true')
    expect(pwa.deferredPrompt.value).toBeNull()
  })

  it('affiche des instructions au lieu d\'une alerte quand l\'installation directe est impossible', async () => {
    const alerte = vi.spyOn(window, 'alert').mockImplementation(() => {})
    const { usePWA, INSTRUCTIONS_MENU } = await charger()
    const { installPWA, instructions } = usePWA()

    await installPWA()

    expect(instructions.value).toBe(INSTRUCTIONS_MENU)
    expect(alerte).not.toHaveBeenCalled()
  })

  it('donne les instructions iOS sur iPhone', async () => {
    vi.spyOn(navigator, 'userAgent', 'get').mockReturnValue('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)')
    const { usePWA, INSTRUCTIONS_IOS } = await charger()
    const { installPWA, instructions } = usePWA()

    await installPWA()

    expect(instructions.value).toBe(INSTRUCTIONS_IOS)
  })

  it('propose la fenêtre une seule fois sur mobile', async () => {
    const { usePWA } = await charger()
    const pwa = usePWA()

    pwa.checkMobileAppPopup(true)
    vi.advanceTimersByTime(1000)
    expect(pwa.showMobileAppPopup.value).toBe(true)

    pwa.closeMobileAppPopup()
    const ensuite = usePWA()
    ensuite.checkMobileAppPopup(true)
    vi.advanceTimersByTime(1000)
    expect(ensuite.showMobileAppPopup.value).toBe(false)
  })

  it('ne propose pas l\'installation dans l\'application déjà installée', async () => {
    // jsdom n'a pas matchMedia : on simule l'affichage d'une application installée
    vi.stubGlobal('matchMedia', (requete) => ({ matches: requete === '(display-mode: standalone)' }))
    const { usePWA } = await charger()
    const pwa = usePWA()

    pwa.checkMobileAppPopup(true)
    vi.advanceTimersByTime(1000)

    expect(pwa.installee.value).toBe(true)
    expect(pwa.showMobileAppPopup.value).toBe(false)
  })

  it('oublie l\'événement une fois l\'application installée', async () => {
    const { usePWA } = await charger()
    window.dispatchEvent(evenementInstallation())
    window.dispatchEvent(new Event('appinstalled'))

    const pwa = usePWA()
    expect(pwa.installee.value).toBe(true)
    expect(pwa.deferredPrompt.value).toBeNull()
  })
})
