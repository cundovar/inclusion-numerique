import { test, before, after, beforeEach } from 'node:test'
import assert from 'node:assert/strict'
import { MongoClient } from 'mongodb'
import { buildApp } from '../src/app.js'
import { ensureIndexes, insertAttempt, COLLECTION } from '../src/attempts.js'

// Tests sur un vrai MongoDB (service `qcm-mongo` en local, service MongoDB en CI), base jetable
const MONGODB_URL = process.env.MONGODB_URL ?? 'mongodb://localhost:27017'
const API_KEY = 'k'.repeat(40)

let client
let db
let app

const answers = (...flags) => flags.map((correct, i) => ({ questionId: i + 1, choiceId: 100 + i, correct }))
const call = (method, url, payload, key = API_KEY) =>
  app.inject({ method, url, payload, headers: key === null ? {} : { 'x-api-key': key } })

before(async () => {
  client = new MongoClient(MONGODB_URL, { serverSelectionTimeoutMS: 5000 })
  await client.connect()
  db = client.db(`qcm_results_test_${process.pid}`)
  await ensureIndexes(db)
  app = buildApp({ db, apiKey: API_KEY })
})

after(async () => {
  await app.close()
  await db.dropDatabase()
  await client.close()
})

beforeEach(async () => {
  await db.collection(COLLECTION).deleteMany({})
})

test('refuse une clé d’API absente ou fausse, sauf pour /health', async () => {
  assert.equal((await call('GET', '/users/1/attempts', undefined, null)).statusCode, 401)
  assert.equal((await call('GET', '/users/1/attempts', undefined, 'x'.repeat(40))).statusCode, 401)
  assert.equal((await call('GET', '/health', undefined, null)).statusCode, 200)
})

test('refuse de démarrer avec une clé trop courte', () => {
  assert.throws(() => buildApp({ db, apiKey: 'court' }), /32 caractères/)
})

test('enregistre une tentative et calcule le score', async () => {
  const response = await call('POST', '/attempts', {
    userId: 3, language: 'CSS', level: 'basique', answers: answers(true, false, true)
  })

  assert.equal(response.statusCode, 201)
  const attempt = response.json()
  assert.equal(attempt.score, 2)
  assert.equal(attempt.total, 3)
  assert.match(attempt.id, /^[0-9a-f]{24}$/)
  assert.equal(await db.collection(COLLECTION).countDocuments({ userId: 3 }), 1)
})

test('valide le corps : champs manquants, inconnus ou mal typés', async () => {
  const valid = { userId: 3, language: 'CSS', level: 'basique', answers: answers(true) }
  const invalid = [
    { ...valid, answers: [] },
    { ...valid, userId: 0 },
    { ...valid, answers: [{ questionId: 1, choiceId: 2 }] },
    { ...valid, score: 10 }
  ]

  for (const body of invalid) {
    assert.equal((await call('POST', '/attempts', body)).statusCode, 400, JSON.stringify(body))
  }
  assert.equal(await db.collection(COLLECTION).countDocuments(), 0)
})

test('historique d’un utilisateur : le plus récent d’abord, sans les réponses, sans les autres utilisateurs', async () => {
  await insertAttempt(db, { userId: 3, language: 'CSS', level: 'basique', answers: answers(true) }, new Date('2026-10-01'))
  await insertAttempt(db, { userId: 3, language: 'PHP', level: 'basique', answers: answers(false) }, new Date('2026-10-05'))
  await insertAttempt(db, { userId: 4, language: 'CSS', level: 'basique', answers: answers(true) }, new Date('2026-10-03'))

  const history = (await call('GET', '/users/3/attempts?limit=10')).json()

  assert.deepEqual(history.map(a => a.language), ['PHP', 'CSS'])
  assert.equal(history[0].answers, undefined)
})

test('progression : meilleur, moyen et dernier score par langage et niveau', async () => {
  await insertAttempt(db, { userId: 3, language: 'CSS', level: 'basique', answers: answers(true, false, false, false) }, new Date('2026-10-01'))
  await insertAttempt(db, { userId: 3, language: 'CSS', level: 'basique', answers: answers(true, true, true, false) }, new Date('2026-10-02'))
  await insertAttempt(db, { userId: 3, language: 'CSS', level: 'basique', answers: answers(true, true, false, false) }, new Date('2026-10-03'))

  const [css] = (await call('GET', '/users/3/progress')).json()

  assert.equal(css.language, 'CSS')
  assert.equal(css.attempts, 3)
  assert.equal(css.bestPercent, 75)
  assert.equal(css.averagePercent, 50)
  assert.equal(css.lastPercent, 50)
  assert.equal(css.lastAttemptAt, '2026-10-03T00:00:00.000Z')
})

test('statistiques par question : la plus ratée en premier, filtrables par langage', async () => {
  // Question 1 : toujours réussie ; question 2 : réussie 1 fois sur 2
  await insertAttempt(db, { userId: 3, language: 'CSS', level: 'basique', answers: answers(true, true) })
  await insertAttempt(db, { userId: 4, language: 'CSS', level: 'basique', answers: answers(true, false) })
  await insertAttempt(db, { userId: 5, language: 'PHP', level: 'basique', answers: answers(false, false) })

  const stats = (await call('GET', '/stats/questions?language=CSS')).json()

  assert.deepEqual(stats, [
    { questionId: 2, answered: 2, correct: 1, successPercent: 50 },
    { questionId: 1, answered: 2, correct: 2, successPercent: 100 }
  ])
})

test('supprime toutes les tentatives d’un utilisateur, et seulement les siennes', async () => {
  await insertAttempt(db, { userId: 3, language: 'CSS', level: 'basique', answers: answers(true) })
  await insertAttempt(db, { userId: 3, language: 'PHP', level: 'basique', answers: answers(true) })
  await insertAttempt(db, { userId: 4, language: 'CSS', level: 'basique', answers: answers(true) })

  assert.deepEqual((await call('DELETE', '/users/3/attempts')).json(), { deleted: 2 })
  assert.equal(await db.collection(COLLECTION).countDocuments(), 1)
})

test('les index de lecture sont créés', async () => {
  const names = (await db.collection(COLLECTION).indexes()).map(i => i.name)
  assert.ok(names.includes('user_history'))
  assert.ok(names.includes('question_stats'))
})
