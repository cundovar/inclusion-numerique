import { after, before, describe, it } from 'node:test'
import assert from 'node:assert/strict'
import { writeFile } from 'node:fs/promises'
import path from 'node:path'
import { CaptureError } from '../../capture/src/capturer.js'
import { createCaptureServer } from '../src/server.js'

const token = 'capture-service-test-token-that-is-long-enough'
let server
let base
let calls = 0

before(async () => {
  server = createCaptureServer({
    token,
    logger: { error() {} },
    async capturePage(request) {
      calls += 1
      const file = path.join(request.outDir, 'capture.jpg')
      await writeFile(file, Buffer.from([0xff, 0xd8, 0xff, 0xd9]))

      return {
        urlFinale: request.url,
        titre: 'Page fictive',
        format: request.format,
        largeur: 1280,
        hauteur: 800,
        elements: request.elements,
        fichier: file,
      }
    },
  })
  await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve))
  base = `http://127.0.0.1:${server.address().port}`
})

after(() => new Promise((resolve) => server.close(resolve)))

describe('service de capture', () => {
  it('expose un état de santé sans révéler le jeton', async () => {
    const response = await fetch(`${base}/health`)

    assert.equal(response.status, 200)
    assert.deepEqual(await response.json(), { ok: true })
  })

  it('refuse une demande sans jeton avant d’ouvrir un navigateur', async () => {
    calls = 0
    const response = await fetch(`${base}/captures`, { method: 'POST', body: '{}' })

    assert.equal(response.status, 401)
    assert.equal(calls, 0)
  })

  it('renvoie la capture et ses métadonnées au client authentifié', async () => {
    const response = await fetch(`${base}/captures`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ url: 'https://www.example.org/', elements: [{ repere: 1, texte: 'Menu' }] }),
    })

    assert.equal(response.status, 200)
    assert.deepEqual(await response.json(), {
      ok: true,
      urlFinale: 'https://www.example.org/',
      titre: 'Page fictive',
      format: 'ordinateur',
      largeur: 1280,
      hauteur: 800,
      elements: [{ repere: 1, texte: 'Menu' }],
      image: '/9j/2Q==',
    })
  })

  it('renvoie le code de refus du moteur sans image', async () => {
    const refusedServer = createCaptureServer({
      token,
      logger: { error() {} },
      async capturePage() { throw new CaptureError('ADRESSE_REFUSEE', 'Adresse refusée.') },
    })
    await new Promise((resolve) => refusedServer.listen(0, '127.0.0.1', resolve))
    const refusedBase = `http://127.0.0.1:${refusedServer.address().port}`
    const response = await fetch(`${refusedBase}/captures`, {
      method: 'POST', headers: { Authorization: `Bearer ${token}` }, body: '{}',
    })

    assert.equal(response.status, 422)
    assert.deepEqual(await response.json(), { ok: false, code: 'ADRESSE_REFUSEE', message: 'Adresse refusée.' })
    await new Promise((resolve) => refusedServer.close(resolve))
  })
})
