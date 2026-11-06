import { after, before, describe, it } from 'node:test'
import assert from 'node:assert/strict'
import { spawn } from 'node:child_process'
import { existsSync } from 'node:fs'
import { mkdtemp, readFile, rm } from 'node:fs/promises'
import http from 'node:http'
import os from 'node:os'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { capture, CaptureError, isCapturableUrl, isPublicUrl } from '../src/capturer.js'

const here = path.dirname(fileURLToPath(import.meta.url))
const sandbox = process.getuid?.() !== 0
// Chromium de la machine (comme sur le serveur) ; sinon celui que Playwright sait trouver
const executablePath = process.env.CAPTURE_CHROMIUM_PATH || (existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined)

// Petit site d'essai : bandeau de cookies, bouton à repérer, formulaires et redirections pièges
const pages = {
  '/accueil': `<!doctype html><html lang="fr"><head><title>Accueil fictif</title></head><body>
    <div id="tarteaucitronRoot" style="position:fixed;bottom:0;left:0;right:0;background:#eee;padding:20px">
      Cookies ? <button onclick="fetch('/consent/accepte')">Tout accepter</button>
      <button onclick="fetch('/consent/refuse');this.parentNode.remove()">Tout refuser</button>
    </div>
    <h1>Site fictif</h1><a href="/connexion" style="display:inline-block;margin:200px 0 0 900px">Mon Compte</a>
    <form method="post" action="/envoi"><input name="q"></form>
    <script>fetch('/api/envoi', { method: 'POST', body: 'x' })</script></body></html>`,
  '/connexion': '<!doctype html><title>Connexion</title><form><input name="id"><input type="password" name="mdp"></form>',
  '/auto-envoi': '<!doctype html><title>Auto</title><form id="f" method="post" action="/envoi"><input name="q" value="1"></form><script>document.getElementById("f").submit()</script>',
}

let server
let base
let tmp
const seen = []

before(async () => {
  tmp = await mkdtemp(path.join(os.tmpdir(), 'capture-test-'))
  server = http.createServer((req, res) => {
    seen.push(`${req.method} ${req.url}`)
    if (req.url === '/anti-robot') {
      res.writeHead(302, { Location: 'https://validate.perfdrive.com/verification' })
      return res.end()
    }
    if (req.url === '/interdit') {
      res.writeHead(403)
      return res.end('interdit')
    }
    if (req.url === '/redirige') {
      res.writeHead(302, { Location: `http://localhost:${server.address().port}/accueil` })
      return res.end()
    }
    const page = pages[req.url]
    res.writeHead(page ? 200 : 404, { 'Content-Type': 'text/html; charset=utf-8' })
    res.end(page ?? 'introuvable')
  })
  await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve))
  base = `http://127.0.0.1:${server.address().port}`
})

after(async () => {
  server.close()
  await rm(tmp, { recursive: true, force: true })
})

const options = (url, extra = {}) => ({ url, allowHttp: true, allowPrivateHosts: ['127.0.0.1'], outDir: tmp, sandbox, executablePath, ...extra })

async function rejectsWith(promise, code) {
  await assert.rejects(promise, (error) => error instanceof CaptureError && error.code === code)
}

describe('domaines capturables', () => {
  it('accepte tout domaine en HTTPS sans identifiants', () => {
    assert.equal(isCapturableUrl('https://www.caf.fr/allocataires'), true)
    assert.equal(isCapturableUrl('https://www.ameli.fr/'), true)
    assert.equal(isCapturableUrl('https://www.caf.fr.example.org/'), true)
    assert.equal(isCapturableUrl('http://www.caf.fr/'), false)
    assert.equal(isCapturableUrl('https://user:mdp@www.caf.fr/'), false)
    assert.equal(isCapturableUrl('pas une adresse'), false)
  })

  it('refuse les adresses IP locales et les noms qui résolvent vers localhost', async () => {
    assert.equal(await isPublicUrl('https://127.0.0.1'), false)
    assert.equal(await isPublicUrl('https://10.1.2.3'), false)
    assert.equal(await isPublicUrl('https://localhost'), false)
  })
})

describe('capture d’une page publique', () => {
  it('refuse les cookies, ne fait que des lectures et repère l’élément demandé', async () => {
    seen.length = 0
    const result = await capture(options(`${base}/accueil`, { elements: [{ repere: 1, texte: 'Mon Compte' }] }))

    assert.equal(result.titre, 'Accueil fictif')
    assert.deepEqual(result.elements, [{ repere: 1, texte: 'Mon Compte' }])
    const image = await readFile(result.fichier)
    assert.deepEqual([...image.subarray(0, 3)], [0xff, 0xd8, 0xff])
    assert.ok(seen.includes('GET /consent/refuse'))
    assert.ok(!seen.some((entry) => entry.includes('/consent/accepte')))
    assert.ok(!seen.some((entry) => !entry.startsWith('GET ')), `requêtes non GET : ${seen.join(', ')}`)
  })

  it('capture au format mobile', async () => {
    const result = await capture(options(`${base}/accueil`, { format: 'mobile' }))

    assert.deepEqual([result.largeur, result.hauteur], [390, 844])
  })

  it('n’envoie jamais un formulaire, même soumis automatiquement par la page', async () => {
    seen.length = 0
    await capture(options(`${base}/auto-envoi`)).catch(() => {})

    assert.ok(!seen.some((entry) => entry.startsWith('POST')), seen.join(', '))
  })
})

describe('refus', () => {
  it('refuse un écran de connexion (CAP-03)', async () => {
    await rejectsWith(capture(options(`${base}/connexion`)), 'ECRAN_CONNEXION')
  })

  it('refuse une redirection vers une adresse privée', async () => {
    await rejectsWith(capture(options(`${base}/redirige`)), 'REDIRECTION_REFUSEE')
  })

  it('reconnaît une protection anti-robots sans chercher à la contourner', async () => {
    seen.length = 0
    await rejectsWith(capture(options(`${base}/anti-robot`)), 'PROTECTION_ANTI_ROBOT')
    await rejectsWith(capture(options(`${base}/interdit`)), 'PROTECTION_ANTI_ROBOT')
    assert.deepEqual(seen, ['GET /anti-robot', 'GET /interdit'])
  })

  it('signale une page absente', async () => {
    await rejectsWith(capture(options(`${base}/absente`)), 'PAGE_INACCESSIBLE')
  })

  it('signale un élément introuvable', async () => {
    await assert.rejects(
      capture(options(`${base}/accueil`, { elements: [{ repere: 1, texte: 'Mon Compte' }, { repere: 2, selecteur: '#inexistant' }] })),
      (error) => error.code === 'ELEMENT_INTROUVABLE' && error.details.length === 1 && error.details[0].repere === 2,
    )
  })
})

describe('commande appelée par le site', () => {
  const run = (request, env = {}) => new Promise((resolve) => {
    const child = spawn(process.execPath, [path.resolve(here, '../src/cli.js')], { env: { ...process.env, ...env } })
    let out = ''
    child.stdout.on('data', (chunk) => { out += chunk })
    child.on('close', (code) => resolve({ code, result: JSON.parse(out) }))
    child.stdin.end(JSON.stringify(request))
  })

  it('n’autorise pas http à la demande de l’appelant', async () => {
    const { code, result } = await run({ url: `${base}/accueil`, allowHttp: true, allowPrivateHosts: ['127.0.0.1'], outDir: tmp })

    assert.equal(code, 2)
    assert.equal(result.code, 'ADRESSE_REFUSEE')
  })

  it('renvoie le résultat en JSON', async () => {
    const env = { CAPTURE_ALLOW_HTTP: '1', CAPTURE_NO_SANDBOX: sandbox ? '0' : '1', CAPTURE_CHROMIUM_PATH: executablePath ?? '' }
    const { code, result } = await run({ url: 'https://127.0.0.1/', outDir: tmp, elements: [{ repere: 1, texte: 'Mon Compte' }] }, env)

    assert.equal(code, 2)
    assert.equal(result.code, 'ADRESSE_REFUSEE')
  })
})
