import Fastify from 'fastify'
import { timingSafeEqual } from 'node:crypto'
import { insertAttempt, findByUser, progressByUser, questionStats, deleteByUser } from './attempts.js'

// API interne : appelée uniquement par Symfony, qui authentifie l'utilisateur et transmet son identifiant.
// Chaque appel (sauf /health) porte la clé partagée dans l'en-tête X-Api-Key.

const userId = { type: 'integer', minimum: 1 }
const label = { type: 'string', minLength: 1, maxLength: 100 }

const attemptBody = {
  type: 'object',
  required: ['userId', 'language', 'level', 'answers'],
  additionalProperties: false,
  properties: {
    userId,
    language: label,
    level: label,
    answers: {
      type: 'array',
      minItems: 1,
      maxItems: 100,
      items: {
        type: 'object',
        required: ['questionId', 'choiceId', 'correct'],
        additionalProperties: false,
        properties: {
          questionId: { type: 'integer', minimum: 1 },
          choiceId: { type: 'integer', minimum: 1 },
          correct: { type: 'boolean' }
        }
      }
    }
  }
}

const userParams = { type: 'object', required: ['userId'], properties: { userId } }
const limitQuery = { type: 'object', properties: { limit: { type: 'integer', minimum: 1, maximum: 100, default: 20 } } }

function sameKey(given, expected) {
  const a = Buffer.from(String(given ?? ''))
  const b = Buffer.from(expected)
  return a.length === b.length && timingSafeEqual(a, b)
}

export function buildApp({ db, apiKey, logger = false }) {
  if (!apiKey || apiKey.length < 32) {
    throw new Error('QCM_RESULTS_API_KEY doit contenir au moins 32 caractères')
  }

  // Champs inconnus refusés (400) au lieu d'être retirés en silence
  const app = Fastify({ logger, ajv: { customOptions: { removeAdditional: false } } })

  app.addHook('onRequest', async (request, reply) => {
    if (request.routeOptions.url === '/health') return
    if (!sameKey(request.headers['x-api-key'], apiKey)) {
      return reply.code(401).send({ error: 'Clé d\'API invalide' })
    }
  })

  app.get('/health', async () => {
    await db.command({ ping: 1 })
    return { status: 'ok' }
  })

  app.post('/attempts', { schema: { body: attemptBody } }, async (request, reply) => {
    const attempt = await insertAttempt(db, request.body)
    return reply.code(201).send(attempt)
  })

  app.get('/users/:userId/attempts', { schema: { params: userParams, querystring: limitQuery } }, async (request) => {
    return findByUser(db, request.params.userId, request.query.limit)
  })

  app.get('/users/:userId/progress', { schema: { params: userParams } }, async (request) => {
    return progressByUser(db, request.params.userId)
  })

  app.delete('/users/:userId/attempts', { schema: { params: userParams } }, async (request) => {
    return { deleted: await deleteByUser(db, request.params.userId) }
  })

  app.get('/stats/questions', {
    schema: {
      querystring: {
        type: 'object',
        properties: { language: label, level: label, limit: limitQuery.properties.limit }
      }
    }
  }, async (request) => {
    const { language, level, limit } = request.query
    return questionStats(db, { language, level }, limit)
  })

  return app
}
