/**
 * Met en forme un cours d'après les titres des gabarits (regles/modeles/) : chaque titre de niveau 2
 * ouvre une partie, reconnue par son intitulé (étape, attention, exercice…), que la feuille de style
 * content-cours.css habille. Les cours n'ont rien à changer : un titre inconnu donne une partie simple.
 * Le HTML produit passe ensuite par SafeHtml.
 */

// Intitulés des gabarits, comparés sans accents ni majuscules
const KINDS = [
  { kind: 'attention', pattern: /^(attention|points? de vigilance)\b/ },
  { kind: 'exercice', pattern: /^a vous\b/ },
  { kind: 'aide', pattern: /^(si ca ne marche pas|quand orienter)\b/ },
  { kind: 'repere', pattern: /^(avant de commencer|ce que vous allez apprendre|ce qu.il vous faut|objectif|pour qui|a quoi sert)\b/ },
]

// « Étape 2 : Ouvrir la messagerie » (gabarit apprendre) ou « 2. Montrer » (gabarit accompagner)
const STEP = /^\s*(?:(étape|etape)\s+(\d+)\s*[:.\-–—]?|(\d+)\s*[.)])\s*(.*)$/is

export function normalize(text) {
  return (text ?? '')
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .replace(/[’']/g, "'")
    .trim()
    .toLowerCase()
}

export function sectionKind(title) {
  const text = normalize(title)
  if (STEP.test(text)) return 'etape'
  return KINDS.find(({ pattern }) => pattern.test(text))?.kind ?? 'simple'
}

/**
 * @param {string} html contenu du cours
 * @returns {string}
 */
export function structureCourse(html) {
  const doc = new DOMParser().parseFromString(html ?? '', 'text/html')
  const body = doc.body
  const headings = [...body.children].filter((node) => node.tagName === 'H2')
  if (headings.length === 0) return body.innerHTML

  const stepCount = headings.filter((h2) => (STEP.exec(h2.textContent)?.[1] ?? null) !== null).length

  for (const h2 of headings) {
    const section = doc.createElement('div')
    section.className = `cours-section cours-section--${sectionKind(h2.textContent)}`
    h2.before(section)

    // La partie va jusqu'au titre de niveau 2 suivant
    let node = h2
    while (node && !(node !== h2 && node.nodeType === 1 && node.tagName === 'H2')) {
      const next = node.nextSibling
      section.append(node)
      node = next
    }

    const step = STEP.exec(h2.textContent)
    if (step) decorateStep(doc, h2, step, stepCount)
  }

  return body.innerHTML
}

// Numéro dans une pastille (data-etape, voir la feuille de style) et, pour les étapes du gabarit
// « apprendre », le rappel « Étape 2 sur 5 » au-dessus du titre
function decorateStep(doc, h2, step, stepCount) {
  const [, word, stepNumber, listNumber, rest] = step
  const number = stepNumber ?? listNumber
  h2.classList.add('cours-etape')
  h2.setAttribute('data-etape', number)
  if (!word || rest.trim() === '') return

  const label = doc.createElement('span')
  label.className = 'cours-etape__label'
  label.textContent = stepCount > 1 ? `Étape ${number} sur ${stepCount}` : `Étape ${number}`
  const title = doc.createElement('span')
  title.className = 'cours-etape__titre'
  title.textContent = rest.trim()
  h2.replaceChildren(label, ' ', title)
}
