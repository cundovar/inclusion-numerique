#!/usr/bin/env node
import { capture, CaptureError } from './capturer.js'

/**
 * Appelé par le site (Symfony) : paramètres en JSON sur l'entrée standard, résultat en JSON sur la sortie.
 * Code de sortie 0 si la capture est faite, 2 si elle est refusée, 1 en cas d'erreur technique.
 */
const chunks = []
for await (const chunk of process.stdin) chunks.push(chunk)

let request
try {
  request = JSON.parse(Buffer.concat(chunks).toString('utf8'))
} catch {
  process.stdout.write(JSON.stringify({ ok: false, code: 'REQUETE_INVALIDE', message: 'Paramètres JSON attendus sur l’entrée standard.' }))
  process.exit(1)
}

try {
  const result = await capture({
    url: request.url,
    elements: request.elements ?? [],
    format: request.format,
    outDir: request.outDir,
    // http seulement pour les tests locaux, jamais à la demande de l'appelant
    allowHttp: process.env.CAPTURE_ALLOW_HTTP === '1',
    executablePath: process.env.CAPTURE_CHROMIUM_PATH || undefined,
    sandbox: process.env.CAPTURE_NO_SANDBOX !== '1',
  })
  process.stdout.write(JSON.stringify({ ok: true, ...result }))
} catch (error) {
  const known = error instanceof CaptureError
  process.stdout.write(JSON.stringify({
    ok: false,
    code: known ? error.code : 'ERREUR_TECHNIQUE',
    message: known ? error.message : `Capture impossible : ${error.message.split('\n')[0]}`,
    details: known ? error.details : undefined,
  }))
  process.exit(known ? 2 : 1)
}
