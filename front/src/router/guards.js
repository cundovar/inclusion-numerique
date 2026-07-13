import { useData } from '@/utils/fetchDataPwa'

/**
 * Guard pour les routes necessitant une authentification
 */
export function authGuard(to, from, next) {
  const { user } = useData()

  if (!user.value || !user.value.username) {
    // Sauvegarder la destination pour rediriger apres login
    sessionStorage.setItem('redirectAfterLogin', to.fullPath)
    // Rediriger vers la page d'accueil (ou login si vous en avez une)
    next({ path: '/' })
  } else {
    next()
  }
}

/**
 * Guard pour les routes admin uniquement
 */
export function adminGuard(to, from, next) {
  const { user } = useData()

  const isAdmin = user.value?.roles?.includes('ROLE_ADMIN')

  if (!isAdmin) {
    next({ path: '/' })
  } else {
    next()
  }
}

/**
 * Guard pour les routes accessibles uniquement aux invites (non connectes)
 */
export function guestGuard(to, from, next) {
  const { user } = useData()

  if (user.value && user.value.username) {
    next({ path: '/' })
  } else {
    next()
  }
}
