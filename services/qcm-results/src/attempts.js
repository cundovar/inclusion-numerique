// Accès MongoDB : une tentative de QCM = un document, réponses imbriquées.
//
// {
//   userId: 3, language: 'CSS', level: 'basique',
//   score: 7, total: 10,
//   answers: [{ questionId: 12, choiceId: 45, correct: true }, …],
//   createdAt: ISODate(…)
// }

export const COLLECTION = 'attempts'

export async function ensureIndexes(db) {
  const attempts = db.collection(COLLECTION)
  await attempts.createIndexes([
    // Historique et progression d'un utilisateur, du plus récent au plus ancien
    { key: { userId: 1, createdAt: -1 }, name: 'user_history' },
    // Statistiques par question, filtrées par langage et niveau
    { key: { language: 1, level: 1, 'answers.questionId': 1 }, name: 'question_stats' }
  ])
}

export async function insertAttempt(db, { userId, language, level, answers }, now = new Date()) {
  const score = answers.filter(a => a.correct).length
  const doc = { userId, language, level, score, total: answers.length, answers, createdAt: now }
  const { insertedId } = await db.collection(COLLECTION).insertOne(doc)
  return toAttempt({ _id: insertedId, ...doc })
}

export async function findByUser(db, userId, limit = 20) {
  const docs = await db.collection(COLLECTION)
    .find({ userId }, { projection: { answers: 0 } })
    .sort({ createdAt: -1 })
    .limit(limit)
    .toArray()
  return docs.map(toAttempt)
}

// Progression d'un utilisateur par langage et niveau
export async function progressByUser(db, userId) {
  return db.collection(COLLECTION).aggregate([
    { $match: { userId } },
    { $sort: { createdAt: 1 } },
    {
      $group: {
        _id: { language: '$language', level: '$level' },
        attempts: { $sum: 1 },
        bestPercent: { $max: { $multiply: [{ $divide: ['$score', '$total'] }, 100] } },
        averagePercent: { $avg: { $multiply: [{ $divide: ['$score', '$total'] }, 100] } },
        lastPercent: { $last: { $multiply: [{ $divide: ['$score', '$total'] }, 100] } },
        lastAttemptAt: { $last: '$createdAt' }
      }
    },
    {
      $project: {
        _id: 0,
        language: '$_id.language',
        level: '$_id.level',
        attempts: 1,
        bestPercent: { $round: ['$bestPercent', 0] },
        averagePercent: { $round: ['$averagePercent', 0] },
        lastPercent: { $round: ['$lastPercent', 0] },
        lastAttemptAt: 1
      }
    },
    { $sort: { lastAttemptAt: -1 } }
  ]).toArray()
}

// Taux de réussite par question, des plus ratées aux mieux réussies
export async function questionStats(db, { language, level } = {}, limit = 20) {
  const match = {}
  if (language) match.language = language
  if (level) match.level = level

  return db.collection(COLLECTION).aggregate([
    { $match: match },
    { $unwind: '$answers' },
    {
      $group: {
        _id: '$answers.questionId',
        answered: { $sum: 1 },
        correct: { $sum: { $cond: ['$answers.correct', 1, 0] } }
      }
    },
    {
      $project: {
        _id: 0,
        questionId: '$_id',
        answered: 1,
        correct: 1,
        successPercent: { $round: [{ $multiply: [{ $divide: ['$correct', '$answered'] }, 100] }, 0] }
      }
    },
    { $sort: { successPercent: 1, answered: -1, questionId: 1 } },
    { $limit: limit }
  ]).toArray()
}

export async function deleteByUser(db, userId) {
  const { deletedCount } = await db.collection(COLLECTION).deleteMany({ userId })
  return deletedCount
}

function toAttempt({ _id, ...doc }) {
  return { id: _id.toHexString(), ...doc }
}
