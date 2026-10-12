import { describe, expect, it } from 'vitest'
import { buildAttemptPayload } from '@/services/qcmResultsService'

// Tentative envoyée à /api/qcm-results : choix seulement, la correction est faite côté serveur
const qcms = [
  { id: 1, choicesQCMs: [{ id: 11, isCorrect: true }, { id: 12, isCorrect: false }] },
  { id: 2, choicesQCMs: [{ id: 21, isCorrect: false }, { id: 22, isCorrect: true }] },
  { id: 3, choicesQCMs: [{ id: 31, isCorrect: true }] }
]

describe('buildAttemptPayload', () => {
  it('associe chaque question au choix retenu, sans score ni bonne réponse', () => {
    const payload = buildAttemptPayload(qcms, [12, 22, 31], { language: 'CSS', niveau: 'basique' })

    expect(payload).toEqual({
      language: 'CSS',
      level: 'basique',
      answers: [
        { questionId: 1, choiceId: 12 },
        { questionId: 2, choiceId: 22 },
        { questionId: 3, choiceId: 31 }
      ]
    })
    expect(JSON.stringify(payload)).not.toMatch(/correct|score/i)
  })

  it('ignore les questions laissées sans réponse', () => {
    const answers = []
    answers[1] = 21

    expect(buildAttemptPayload(qcms, answers, { language: 'CSS', niveau: 'basique' }).answers)
      .toEqual([{ questionId: 2, choiceId: 21 }])
  })

  it('reste exploitable sans filtres', () => {
    expect(buildAttemptPayload([], [], null)).toEqual({ language: '', level: '', answers: [] })
  })
})
