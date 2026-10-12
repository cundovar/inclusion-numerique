import api from './api'

/**
 * Construit la tentative envoyée au serveur : uniquement les choix, jamais le score
 * (Symfony corrige à partir de la base, puis le micro-service MongoDB stocke).
 * Les questions sans réponse sont ignorées.
 */
export function buildAttemptPayload(qcms, answers, filter) {
  const payloadAnswers = qcms
    .map((qcm, index) => ({ questionId: qcm.id, choiceId: answers[index] }))
    .filter((answer) => Number.isInteger(answer.questionId) && Number.isInteger(answer.choiceId))

  return {
    language: filter?.language ?? '',
    level: filter?.niveau ?? '',
    answers: payloadAnswers
  }
}

export const qcmResultsService = {
  /**
   * Enregistre une tentative ; renvoie la tentative stockée (score, total, date)
   */
  async recordAttempt(payload) {
    const { data } = await api.post('/qcm-results', payload)
    return data
  },

  /**
   * Progression par langage et niveau (meilleur, moyen et dernier score)
   */
  async getMyProgress() {
    const { data } = await api.get('/qcm-results/me/progress')
    return data
  },

  /**
   * Dernières tentatives, la plus récente d'abord
   */
  async getMyAttempts(limit = 10) {
    const { data } = await api.get('/qcm-results/me', { params: { limit } })
    return data
  },

  /**
   * Efface tout l'historique de QCM de l'utilisateur
   */
  async clearMyAttempts() {
    const { data } = await api.delete('/qcm-results/me')
    return data
  }
}

export default qcmResultsService
