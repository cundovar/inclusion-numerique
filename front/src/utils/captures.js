/**
 * Remplace les marqueurs [capture:ID] d'un cours par la capture correspondante :
 * image, légende, source et date. Le HTML produit passe ensuite par SafeHtml.
 *
 * Un marqueur seul dans son paragraphe remplace tout le paragraphe ; au milieu d'une phrase,
 * la capture suit le paragraphe. Une capture introuvable (supprimée, hors ligne) fait
 * disparaître le marqueur sans casser le texte.
 */
export const CAPTURE_MARKER = /\[capture:(\d+)\]/g

export function findCaptureIds(html) {
  return [...new Set([...(html ?? '').matchAll(CAPTURE_MARKER)].map((match) => Number(match[1])))]
}

/**
 * @param {string} html contenu du cours
 * @param {(ids: number[]) => Promise<Array>} fetchCaptures charge les captures demandées
 */
export async function expandCaptures(html, fetchCaptures) {
  const ids = findCaptureIds(html)
  if (ids.length === 0) return html ?? ''

  let captures = []
  try {
    captures = await fetchCaptures(ids)
  } catch (error) {
    console.warn('Captures indisponibles, le cours est affiché sans elles.', error)
  }
  const byId = new Map(captures.map((capture) => [Number(capture.id), capture]))

  const doc = new DOMParser().parseFromString(html, 'text/html')
  const walker = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT)
  const textNodes = []
  while (walker.nextNode()) {
    if (CAPTURE_MARKER.test(walker.currentNode.nodeValue)) textNodes.push(walker.currentNode)
    CAPTURE_MARKER.lastIndex = 0
  }

  for (const node of textNodes) {
    const parent = node.parentNode
    const figures = []
    const text = node.nodeValue.replace(CAPTURE_MARKER, (marker, id) => {
      const capture = byId.get(Number(id))
      if (capture?.url) figures.push(buildFigure(doc, capture))
      return ''
    })

    // Une figure ne peut pas être dans un paragraphe : elle suit la phrase, et un marqueur seul remplace le paragraphe
    const paragraph = parent.closest('p')
    if (paragraph) {
      node.nodeValue = text
      paragraph.after(...figures)
      if (paragraph.textContent.trim() === '' && paragraph.querySelector('img, br') === null) paragraph.remove()
    } else {
      node.replaceWith(text, ...figures)
    }
  }

  return doc.body.innerHTML
}

// Construit la figure avec l'API DOM : aucune donnée n'est interprétée comme du HTML
function buildFigure(doc, capture) {
  const figure = doc.createElement('figure')
  figure.className = 'capture-figure'
  // RGAA 1.9 : légende reliée à l'image par un rôle de figure et un nom identique à la légende
  figure.setAttribute('role', 'figure')
  figure.setAttribute('aria-label', capture.caption ?? '')

  const img = doc.createElement('img')
  img.setAttribute('src', capture.url)
  img.setAttribute('alt', capture.altText ?? '')
  if (capture.width) img.setAttribute('width', String(capture.width))
  if (capture.height) img.setAttribute('height', String(capture.height))
  img.setAttribute('loading', 'lazy')
  figure.append(img)

  const caption = doc.createElement('figcaption')
  const legend = doc.createElement('p')
  legend.className = 'capture-caption'
  legend.textContent = capture.caption ?? ''
  caption.append(legend)

  const source = doc.createElement('p')
  source.className = 'capture-source'
  if (capture.kind === 'illustration') {
    source.textContent = "Illustration pédagogique : ce n'est pas l'écran réel du service."
  } else {
    const service = capture.service ? ` ${capture.service}` : ''
    source.append(`Capture du site${service}${formatDate(capture.capturedAt)}.`)
    if (capture.sourceUrl) {
      source.append(' ')
      const link = doc.createElement('a')
      link.setAttribute('href', capture.sourceUrl)
      link.setAttribute('target', '_blank')
      link.textContent = 'Voir la page officielle'
      source.append(link)
    }
  }
  caption.append(source)

  const zoom = doc.createElement('a')
  zoom.className = 'capture-zoom'
  zoom.setAttribute('href', capture.url)
  zoom.setAttribute('target', '_blank')
  zoom.textContent = "Agrandir l'image"
  caption.append(zoom)

  figure.append(caption)
  return figure
}

function formatDate(isoDate) {
  if (!isoDate) return ''
  const [year, month, day] = String(isoDate).slice(0, 10).split('-')
  return year && month && day ? `, le ${day}/${month}/${year}` : ''
}
