import axios from 'axios'

// Instance Axios dediee au microservice IA QCM
const qcmApi = axios.create({
  // baseURL: 'https://localhost:8002/api', // LOCAL
  baseURL: 'https://ia-qcm.varascundo.com/api', // PROD
  timeout: 30000, // Timeout plus long pour la generation IA
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Intercepteur pour gerer les erreurs
qcmApi.interceptors.response.use(
  (response) => response,
  (error) => {
    const message = error.response?.data?.error || error.message || 'Erreur inconnue'
    return Promise.reject(new Error(message))
  }
)

export const qcmService = {
  /**
   * Genere un nouveau QCM
   */
  async generateQCM(options = {}) {
    const { data } = await qcmApi.post('/qcm/generate', {
      categoryId: options.categoryId || null,
      difficulty: options.difficulty || 'medium',
      questionsCount: options.questionsCount || 10,
      userId: options.userId || null
    })
    return data
  },

  /**
   * Recupere une session QCM
   */
  async getSession(sessionId) {
    const { data } = await qcmApi.get(`/qcm/session/${sessionId}`)
    return data
  },

  /**
   * Soumet une reponse
   */
  async submitAnswer(sessionId, questionId, answer, timeSpent = 0, userId = null) {
    const { data } = await qcmApi.post(`/qcm/session/${sessionId}/answer`, {
      questionId,
      answer,
      timeSpent,
      userId
    })
    return data
  },

  /**
   * Complete une session QCM
   */
  async completeSession(sessionId) {
    const { data } = await qcmApi.post(`/qcm/session/${sessionId}/complete`)
    return data
  },

  /**
   * Recupere les sessions d'un utilisateur
   */
  async getUserSessions(userId, limit = 10) {
    const params = new URLSearchParams()
    if (userId) params.append('userId', userId)
    if (limit) params.append('limit', limit)

    const { data } = await qcmApi.get(`/qcm/sessions?${params}`)
    return data
  },

  /**
   * Verifie la sante du service
   */
  async checkServiceHealth() {
    try {
      await qcmApi.get('/content/health')
      return true
    } catch {
      return false
    }
  },

  // Methodes utilitaires
  getDifficultyLabel(difficulty) {
    const labels = {
      'easy': 'Facile',
      'medium': 'Moyen',
      'hard': 'Difficile'
    }
    return labels[difficulty] || difficulty
  },

  calculateGrade(score, total) {
    if (total === 0) return 'N/A'

    const percentage = (score / total) * 100

    if (percentage >= 90) return 'Excellent'
    if (percentage >= 80) return 'Tres bien'
    if (percentage >= 70) return 'Bien'
    if (percentage >= 60) return 'Assez bien'
    if (percentage >= 50) return 'Passable'
    return 'Insuffisant'
  },

  getGradeColor(score, total) {
    const percentage = (score / total) * 100

    if (percentage >= 80) return 'success'
    if (percentage >= 60) return 'warning'
    return 'danger'
  }
}

// Export par defaut pour retrocompatibilite
export default qcmService
