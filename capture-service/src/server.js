import { createServer } from 'node:http'
import { mkdtemp, readFile, rm } from 'node:fs/promises'
import os from 'node:os'
import path from 'node:path'
import { timingSafeEqual } from 'node:crypto'
import { capture, CaptureError } from '../../capture/src/capturer.js'

const MAX_BODY_BYTES = 64 * 1024
const MAX_IMAGE_BYTES = 10 * 1024 * 1024

function sendJson(response, status, body) {
  const payload = JSON.stringify(body)
  response.writeHead(status, {
    'Content-Type': 'application/json; charset=utf-8',
    'Content-Length': Buffer.byteLength(payload),
    'Cache-Control': 'no-store',
  })
  response.end(payload)
}

function authorized(request, token) {
  const expected = Buffer.from(`Bearer ${token}`)
  const supplied = Buffer.from(request.headers.authorization ?? '')

  return expected.length === supplied.length && timingSafeEqual(expected, supplied)
}

async function readRequest(request) {
  const chunks = []
  let size = 0
  for await (const chunk of request) {
    size += chunk.length
    if (size > MAX_BODY_BYTES) throw new CaptureError('REQUETE_INVALIDE', 'La demande de capture est trop volumineuse.')
    chunks.push(chunk)
  }
  try {
    const body = JSON.parse(Buffer.concat(chunks).toString('utf8'))
    if (!body || Array.isArray(body) || typeof body !== 'object') throw new Error('object expected')

    return body
  } catch {
    throw new CaptureError('REQUETE_INVALIDE', 'La demande de capture doit être un objet JSON.')
  }
}

function publicResult(result, image) {
  return {
    ok: true,
    urlFinale: result.urlFinale,
    titre: result.titre,
    format: result.format,
    largeur: result.largeur,
    hauteur: result.hauteur,
    elements: result.elements,
    image: image.toString('base64'),
  }
}

export function createCaptureServer({ token, capturePage = capture, logger = console }) {
  if (typeof token !== 'string' || token.length < 32) {
    throw new Error('CAPTURE_SERVICE_TOKEN must contain at least 32 characters.')
  }

  return createServer(async (request, response) => {
    if (request.method === 'GET' && request.url === '/health') {
      return sendJson(response, 200, { ok: true })
    }
    if (request.method !== 'POST' || request.url !== '/captures') {
      return sendJson(response, 404, { ok: false, code: 'INTROUVABLE', message: 'Route introuvable.' })
    }
    if (!authorized(request, token)) {
      return sendJson(response, 401, { ok: false, code: 'NON_AUTORISE', message: 'Jeton de service invalide.' })
    }

    let directory
    try {
      const input = await readRequest(request)
      directory = await mkdtemp(path.join(os.tmpdir(), 'capture-'))
      const result = await capturePage({
        url: input.url,
        elements: input.elements ?? [],
        format: input.format ?? 'ordinateur',
        outDir: directory,
        sandbox: false,
      })
      const image = await readFile(result.fichier)
      if (image.length === 0 || image.length > MAX_IMAGE_BYTES) {
        throw new CaptureError('ERREUR_TECHNIQUE', 'La taille de l’image générée est invalide.')
      }

      return sendJson(response, 200, publicResult(result, image))
    } catch (error) {
      if (error instanceof CaptureError) {
        return sendJson(response, 422, { ok: false, code: error.code, message: error.message, details: error.details })
      }
      logger.error('Capture service error', error)

      return sendJson(response, 500, { ok: false, code: 'ERREUR_TECHNIQUE', message: 'Le service de capture a rencontré une erreur.' })
    } finally {
      if (directory) await rm(directory, { recursive: true, force: true })
    }
  })
}

if (import.meta.url === `file://${process.argv[1]}`) {
  const server = createCaptureServer({ token: process.env.CAPTURE_SERVICE_TOKEN ?? '' })
  const port = Number(process.env.PORT ?? 3000)
  server.listen(port, '0.0.0.0', () => console.log(`Capture service listening on ${port}`))
}
