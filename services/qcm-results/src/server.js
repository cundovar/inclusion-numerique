import { MongoClient } from 'mongodb'
import { buildApp } from './app.js'
import { ensureIndexes } from './attempts.js'

const { MONGODB_URL, MONGODB_DB = 'devdoc', QCM_RESULTS_API_KEY, PORT = '3000' } = process.env

if (!MONGODB_URL) {
  console.error('MONGODB_URL est obligatoire')
  process.exit(1)
}

const client = new MongoClient(MONGODB_URL)
await client.connect()
const db = client.db(MONGODB_DB)
await ensureIndexes(db)

const app = buildApp({ db, apiKey: REDACTED, logger: true })

const shutdown = async () => {
  await app.close()
  await client.close()
  process.exit(0)
}
process.on('SIGTERM', shutdown)
process.on('SIGINT', shutdown)

await app.listen({ host: '0.0.0.0', port: Number(PORT) })
