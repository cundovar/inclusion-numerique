import axios from 'axios'

const LOGOUT_EVENT = 'auth:logout'
const LOGOUT_TOKEN_SELECTOR = 'meta[name="logout-csrf-token"]'

const authChannel = typeof BroadcastChannel === 'undefined'
  ? null
  : new BroadcastChannel('devdoc-auth')

export function clearAuthenticatedClientState() {
  sessionStorage.removeItem('token')
  sessionStorage.removeItem('redirectAfterLogin')
  window.dispatchEvent(new Event(LOGOUT_EVENT))
}

if (authChannel) {
  authChannel.onmessage = ({ data }) => {
    if (data === LOGOUT_EVENT) {
      clearAuthenticatedClientState()
    }
  }
}

export async function logout() {
  const csrfToken = document.querySelector(LOGOUT_TOKEN_SELECTOR)?.content

  if (!csrfToken) {
    throw new Error('Le jeton CSRF de déconnexion est introuvable.')
  }

  await axios.post('/logout', new URLSearchParams({ _csrf_token: csrfToken }), {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })

  clearAuthenticatedClientState()
  authChannel?.postMessage(LOGOUT_EVENT)
}

export { LOGOUT_EVENT }
