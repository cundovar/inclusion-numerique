import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/ld+json',
    'Accept': 'application/ld+json'
  }
})

// Intercepteur requetes
api.interceptors.request.use(
  (config) => {
    // Ajouter token si present
    const token = sessionStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Intercepteur reponses
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const { response } = error

    // Erreur reseau (offline)
    if (!response) {
      console.warn('[API] Erreur reseau - Mode offline possible')
      error.isNetworkError = true
      return Promise.reject(error)
    }

    // Ajouter des proprietes utiles a l'erreur
    error.statusCode = response.status
    error.errorMessage = response.data?.message || response.data?.error || error.message

    // Non authentifie
    if (response.status === 401) {
      sessionStorage.removeItem('token')
      console.warn('[API] Session expiree ou non authentifie')
      error.isAuthError = true
    }

    // Forbidden
    if (response.status === 403) {
      console.error('[API] Acces refuse')
      error.isForbidden = true
    }

    // Not Found
    if (response.status === 404) {
      console.warn('[API] Ressource non trouvee')
      error.isNotFound = true
    }

    // Validation error
    if (response.status === 422) {
      console.warn('[API] Erreur de validation')
      error.isValidationError = true
      error.validationErrors = response.data?.errors || {}
    }

    // Erreur serveur
    if (response.status >= 500) {
      console.error('[API] Erreur serveur')
      error.isServerError = true
    }

    return Promise.reject(error)
  }
)

export default api
