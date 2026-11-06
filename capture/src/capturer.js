import { mkdir, writeFile } from 'node:fs/promises'
import { lookup } from 'node:dns/promises'
import { BlockList, isIP } from 'node:net'
import path from 'node:path'
import { chromium } from 'playwright-core'

/**
 * Capture d'une page officielle, selon les règles CAP-01 à CAP-07 (regles/05-captures.md) :
 * - tout domaine public en HTTPS, sauf adresses locales/privées, y compris après redirection ;
 * - aucune requête autre que GET : aucun formulaire ne peut être envoyé ;
 * - refus d'un écran de connexion (champ mot de passe visible) : il faut alors une illustration fictive ;
 * - bandeau de cookies refusé ou masqué, jamais accepté ;
 * - repères numérotés dessinés sur les éléments à montrer.
 */
export const FORMATS = {
  ordinateur: { viewport: { width: 1280, height: 800 }, isMobile: false },
  mobile: { viewport: { width: 390, height: 844 }, isMobile: true },
}

export class CaptureError extends Error {
  constructor(code, message, details = undefined) {
    super(message)
    this.code = code
    this.details = details
  }
}

// Boutons de refus des bandeaux de consentement courants ; on ne clique jamais sur « accepter »
const REFUSE_BUTTON = /^(tout refuser|refuser tout|refuser|je refuse|continuer sans accepter|non merci)$/i
const CONSENT_CONTAINERS = ['#tarteaucitronRoot', '#didomi-host', '#onetrust-consent-sdk', '#axeptio_overlay', '.fr-consent-banner', '#cookie-banner', '.cookie-banner']

// Services anti-robots connus : on ne cherche jamais à les contourner, on oriente vers une capture à la main
const BOT_PROTECTION_HOSTS = /(^|\.)(perfdrive\.com|captcha-delivery\.com|hcaptcha\.com|challenges\.cloudflare\.com|datadome\.co)$/i
const BOT_PROTECTION_MESSAGE = "Ce site bloque les navigateurs automatiques (protection anti-robots). Ne pas la contourner : faites la capture à la main, puis déposez-la dans le back-office (menu « Captures »)."

function isBotProtection(rawUrl) {
  try {
    return BOT_PROTECTION_HOSTS.test(new URL(rawUrl).hostname)
  } catch {
    return false
  }
}

const PRIVATE_ADDRESSES = new BlockList()
for (const [subnet, prefix] of [
  ['0.0.0.0', 8], ['10.0.0.0', 8], ['100.64.0.0', 10], ['127.0.0.0', 8], ['169.254.0.0', 16],
  ['172.16.0.0', 12], ['192.0.0.0', 24], ['192.0.2.0', 24], ['192.168.0.0', 16], ['198.18.0.0', 15],
  ['198.51.100.0', 24], ['203.0.113.0', 24], ['224.0.0.0', 4], ['240.0.0.0', 4],
]) PRIVATE_ADDRESSES.addSubnet(subnet, prefix, 'ipv4')
for (const [subnet, prefix] of [
  ['::', 96], ['fc00::', 7], ['fe80::', 10], ['ff00::', 8], ['2001:db8::', 32], ['64:ff9b:1::', 48],
]) PRIVATE_ADDRESSES.addSubnet(subnet, prefix, 'ipv6')

export function isCapturableUrl(rawUrl, { allowHttp = false } = {}) {
  let url
  try {
    url = new URL(rawUrl)
  } catch {
    return false
  }
  if (url.protocol !== 'https:' && !(allowHttp && url.protocol === 'http:')) return false
  return !url.username && !url.password
}

export async function isPublicUrl(rawUrl, { allowHttp = false, allowPrivateHosts = [] } = {}) {
  if (!isCapturableUrl(rawUrl, { allowHttp })) return false
  const hostname = new URL(rawUrl).hostname.replace(/^\[|\]$/g, '')
  // Les tests peuvent autoriser explicitement leur serveur local, sans ouvrir
  // les redirections vers d'autres hôtes privés.
  if (allowPrivateHosts.includes(hostname)) return true
  const family = isIP(hostname)
  const addresses = family
    ? [{ address: hostname, family }]
    : await lookup(hostname, { all: true, verbatim: true }).catch(() => [])

  return addresses.length > 0 && addresses.every(({ address, family: addressFamily }) =>
    !PRIVATE_ADDRESSES.check(address, addressFamily === 4 ? 'ipv4' : 'ipv6'),
  )
}

/**
 * @param {object} options
 * @param {string} options.url page à capturer
 * @param {Array<{repere: number, selecteur?: string, texte?: string}>} [options.elements] éléments à repérer
 * @param {'ordinateur'|'mobile'} [options.format]
 * @param {string} options.outDir dossier où écrire l'image
 */
export async function capture({ url, elements = [], format = 'ordinateur', outDir, executablePath, sandbox = true, allowHttp = false, allowPrivateHosts = [], timeoutMs = 30000 }) {
  if (!(await isPublicUrl(url, { allowHttp, allowPrivateHosts }))) {
    throw new CaptureError('ADRESSE_REFUSEE', 'Adresse refusée : seules les pages publiques en https sont capturées, hors destinations locales ou privées.')
  }
  const device = FORMATS[format]
  if (!device) throw new CaptureError('FORMAT_INCONNU', `Format inconnu : ${format}.`)

  const browser = await chromium.launch({ executablePath: executablePath || undefined, headless: true, args: sandbox ? [] : ['--no-sandbox'] })
  try {
    const context = await browser.newContext({
      ...device,
      deviceScaleFactor: 1,
      locale: 'fr-FR',
      serviceWorkers: 'block',
      acceptDownloads: false,
    })
    const page = await context.newPage()
    page.setDefaultTimeout(timeoutMs)

    const refused = []
    let rejectedMainNavigation = false
    await page.route('**/*', async (route) => {
      const request = route.request()
      // Lecture seule : aucune requête qui pourrait envoyer des données
      if (request.method() !== 'GET') {
        refused.push(`${request.method()} ${request.url()}`)
        return route.abort('blockedbyclient')
      }
      // Aucun domaine local/privé n'est contacté, y compris via les sous-ressources et les redirections.
      if (!(await isPublicUrl(request.url(), { allowHttp, allowPrivateHosts }))) {
        refused.push(request.url())
        if (request.isNavigationRequest() && request.frame() === page.mainFrame()) rejectedMainNavigation = true
        return route.abort('blockedbyclient')
      }
      return route.continue()
    })

    let response
    try {
      response = await page.goto(url, { waitUntil: 'load', timeout: timeoutMs })
      await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {})
    } catch (error) {
      if (refused.some(isBotProtection)) {
        throw new CaptureError('PROTECTION_ANTI_ROBOT', BOT_PROTECTION_MESSAGE)
      }
      if (rejectedMainNavigation) {
        throw new CaptureError('REDIRECTION_REFUSEE', 'La navigation mène vers une adresse non autorisée (locale, privée ou non HTTPS).', refused)
      }
      throw new CaptureError('PAGE_INACCESSIBLE', `Page inaccessible : ${error.message.split('\n')[0]}`)
    }
    if (response && [403, 429].includes(response.status())) {
      throw new CaptureError('PROTECTION_ANTI_ROBOT', `${BOT_PROTECTION_MESSAGE} (HTTP ${response.status()})`)
    }
    if (!response || response.status() >= 400) {
      throw new CaptureError('PAGE_INACCESSIBLE', `La page répond avec le code HTTP ${response?.status() ?? 'inconnu'}.`)
    }
    if (isBotProtection(page.url())) {
      throw new CaptureError('PROTECTION_ANTI_ROBOT', BOT_PROTECTION_MESSAGE)
    }
    if (!(await isPublicUrl(page.url(), { allowHttp, allowPrivateHosts }))) {
      throw new CaptureError('REDIRECTION_REFUSEE', `La redirection vers ${page.url()} a été refusée.`)
    }

    await dismissConsent(page)

    // CAP-03 : un écran de connexion n'est jamais capturé
    if (await page.locator('input[type="password"]').filter({ visible: true }).count() > 0) {
      throw new CaptureError('ECRAN_CONNEXION', "Cette page affiche un formulaire de connexion : utilisez une illustration fictive (CAP-03).")
    }

    const found = []
    const missing = []
    for (const element of elements) {
      const locator = element.selecteur
        ? page.locator(element.selecteur).first()
        : page.getByText(element.texte ?? '', { exact: false }).filter({ visible: true }).first()
      const visible = await locator.isVisible().catch(() => false)
      if (!visible) {
        missing.push(element)
        continue
      }
      found.push({ element, locator })
    }
    if (missing.length > 0) {
      throw new CaptureError('ELEMENT_INTROUVABLE', 'Élément introuvable ou caché sur la page.', missing)
    }

    if (found.length > 0) {
      await found[0].locator.scrollIntoViewIfNeeded()
      await page.evaluate(() => window.scrollBy(0, -Math.round(window.innerHeight / 4)))
    }
    const marked = []
    for (const { element, locator } of found) {
      const box = await locator.boundingBox()
      const label = ((await locator.innerText().catch(() => '')) || (await locator.getAttribute('aria-label')) || '').trim().replace(/\s+/g, ' ').slice(0, 80)
      marked.push({ repere: element.repere, texte: label, boite: box })
    }
    await drawMarkers(page, marked)

    await mkdir(outDir, { recursive: true })
    const file = path.join(outDir, `capture-${format}.jpg`)
    await writeFile(file, await page.screenshot({ type: 'jpeg', quality: 80 }))

    return {
      urlFinale: page.url(),
      titre: await page.title(),
      format,
      largeur: device.viewport.width,
      hauteur: device.viewport.height,
      elements: marked.map(({ repere, texte }) => ({ repere, texte })),
      fichier: file,
    }
  } finally {
    await browser.close()
  }
}

async function dismissConsent(page) {
  const buttons = page.getByRole('button', { name: REFUSE_BUTTON })
  if (await buttons.count() > 0) {
    await buttons.first().click({ timeout: 3000 }).catch(() => {})
  }
  // Ce qui reste du bandeau est masqué, sans rien accepter
  await page.addStyleTag({ content: `${CONSENT_CONTAINERS.join(',')}{display:none !important}` })
}

// Repères : contour et numéro (ACC-03 : l'information n'est pas donnée seulement par la couleur)
async function drawMarkers(page, marked) {
  await page.evaluate((items) => {
    for (const { repere, boite } of items) {
      const x = boite.x + window.scrollX
      const y = boite.y + window.scrollY
      const frame = document.createElement('div')
      frame.style.cssText = `position:absolute;left:${x - 6}px;top:${y - 6}px;width:${boite.width + 12}px;height:${boite.height + 12}px;border:4px solid #c00000;border-radius:8px;box-shadow:0 0 0 3px #fff;z-index:2147483646;pointer-events:none;`
      const badge = document.createElement('div')
      badge.textContent = String(repere)
      // Le numéro reste dans l'image, même pour un élément collé au bord
      badge.style.cssText = `position:absolute;left:${Math.max(2, x - 22)}px;top:${Math.max(window.scrollY + 2, y - 22)}px;width:32px;height:32px;border-radius:50%;background:#c00000;color:#fff;border:3px solid #fff;font:bold 18px/26px Arial,sans-serif;text-align:center;z-index:2147483647;pointer-events:none;`
      document.body.append(frame, badge)
    }
  }, marked)
}
