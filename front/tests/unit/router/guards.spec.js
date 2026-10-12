import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'

// useData ouvre IndexedDB à l'import : on le remplace par un utilisateur contrôlé
const user = ref({ username: '', roles: [] })
vi.mock('@/utils/fetchDataPwa', () => ({ useData: () => ({ user }) }))

const { authGuard, adminGuard, guestGuard } = await import('@/router/guards')

const run = (guard, to = { fullPath: '/profile' }) => {
  const next = vi.fn()
  guard(to, {}, next)
  return next
}

describe('gardes du routeur', () => {
  beforeEach(() => {
    user.value = { username: '', roles: [] }
    sessionStorage.clear()
  })

  describe('authGuard (US-13)', () => {
    it('redirige un visiteur vers l\'accueil et mémorise la destination', () => {
      const next = run(authGuard)

      expect(next).toHaveBeenCalledWith({ path: '/' })
      expect(sessionStorage.getItem('redirectAfterLogin')).toBe('/profile')
    })

    it('laisse passer un utilisateur connecté', () => {
      user.value = { username: 'alice', roles: ['ROLE_USER'] }

      expect(run(authGuard)).toHaveBeenCalledWith()
    })
  })

  describe('adminGuard (US-14)', () => {
    it('refuse un utilisateur sans ROLE_ADMIN', () => {
      user.value = { username: 'alice', roles: ['ROLE_USER'] }

      expect(run(adminGuard)).toHaveBeenCalledWith({ path: '/' })
    })

    it('laisse passer un administrateur', () => {
      user.value = { username: 'admin', roles: ['ROLE_USER', 'ROLE_ADMIN'] }

      expect(run(adminGuard)).toHaveBeenCalledWith()
    })
  })

  describe('guestGuard', () => {
    it('renvoie un utilisateur connecté vers l\'accueil', () => {
      user.value = { username: 'alice', roles: ['ROLE_USER'] }

      expect(run(guestGuard)).toHaveBeenCalledWith({ path: '/' })
    })

    it('laisse passer un visiteur', () => {
      expect(run(guestGuard)).toHaveBeenCalledWith()
    })
  })
})
