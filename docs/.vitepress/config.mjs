import { fileURLToPath } from 'node:url'
import fs from 'node:fs'
import path from 'node:path'
import { defineConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'

// Site VitePress à la racine de docs/ : les documents restent à côté du code
const SRC_DIR = fileURLToPath(new URL('..', import.meta.url)).replace(/\/$/, '')
const REPO_ROOT = path.resolve(SRC_DIR, '..')
const GITHUB_REPO = 'https://github.com/cundovar/mediateur-numerique-formation'
const GITHUB_BLOB = `${GITHUB_REPO}/blob/main/`

// Notes de travail hors du site, mais toujours lisibles sur GitHub
const EXCLUDED = []

/**
 * Un lien qui pointe vers le code (hors de docs/, dont le dossier VAE/), vers un dossier sans page,
 * vers un fichier qui n'est pas une page (ex. env.example) ou vers une page exclue du site
 * est réécrit vers GitHub au lieu de donner une page morte.
 */
function toGithub(target) {
  if (!target.startsWith(REPO_ROOT + path.sep)) return null
  const rel = path.relative(REPO_ROOT, target)
  if (!target.startsWith(SRC_DIR + path.sep)) return GITHUB_BLOB + rel

  const relDocs = path.relative(SRC_DIR, target)
  const asPage = /\.[a-z0-9]+$/i.test(relDocs) ? relDocs : `${relDocs}.md`
  const exists = fs.existsSync(target)
  const isFolderWithoutPage = exists && fs.statSync(target).isDirectory()
    && !fs.existsSync(path.join(target, 'index.md'))
  const isFileNotPage = exists && fs.statSync(target).isFile() && !target.endsWith('.md')
  const isExcluded = EXCLUDED.some((entry) => asPage === entry || asPage.startsWith(entry))
  if (!isFolderWithoutPage && !isFileNotPage && !isExcluded) return null
  return GITHUB_BLOB + (isExcluded && !exists ? path.relative(REPO_ROOT, path.join(SRC_DIR, asPage)) : rel)
}

function codeLinksToGithub(md) {
  md.core.ruler.after('inline', 'code-links-to-github', (state) => {
    const pageDir = path.dirname(state.env.realPath ?? state.env.path ?? SRC_DIR)
    for (const block of state.tokens) {
      for (const token of block.children ?? []) {
        if (token.type !== 'link_open') continue
        const href = token.attrGet('href')
        if (!href || /^([a-z]+:|#)/i.test(href)) continue
        const file = decodeURI(href.split('#')[0])
        // Lien absolu vers le disque (rare) ou relatif à la page
        const target = file.startsWith(REPO_ROOT) ? file : file.startsWith('/') ? null : path.resolve(pageDir, file)
        const url = target && toGithub(target)
        if (url) {
          token.attrSet('href', url)
          token.attrSet('target', '_blank')
          token.attrSet('rel', 'noreferrer')
        }
      }
    }
  })
}

// Ancres au format GitHub (accents gardés, ponctuation retirée) : un lien #ancre marche sur GitHub et sur le site
const githubSlug = (text) => text.trim().toLowerCase()
  .replace(/<[^>]+>/g, '')
  .replace(/[^\p{L}\p{N}\s_-]/gu, '')
  .replace(/\s/g, '-')

const sidebar = [
  {
    text: 'Présentation',
    items: [
      { text: 'Le projet', link: '/' }
    ]
  },
  {
    text: 'Conception',
    items: [
      { text: 'Besoin et publics', link: '/conception/' },
      { text: 'Reprise de DevDoc', link: '/conception/reprise-devdoc' }
    ]
  },
  {
    text: 'Fonctionnalités',
    items: [
      { text: 'Vue d\'ensemble', link: '/fonctionnalites/' },
      { text: 'Guide d\'édition', link: '/fonctionnalites/edition' },
      { text: 'Connecteur MCP', link: '/fonctionnalites/connecteur' }
    ]
  },
  {
    text: 'Technique',
    items: [
      { text: 'Architecture', link: '/architecture/' },
      { text: 'Sécurité', link: '/securite/' }
    ]
  },
  {
    text: 'Qualité et exploitation',
    items: [
      { text: 'Tests', link: '/tests/' },
      { text: 'Déploiement et CI', link: '/deploiement/' },
      { text: 'Harness des agents', link: '/harness/' }
    ]
  },
  {
    text: 'VAE',
    items: [
      { text: 'Dossier et preuves', link: '/vae/' }
    ]
  }
]

export default withMermaid(defineConfig({
  srcExclude: EXCLUDED.map((entry) => (entry.endsWith('/') ? `${entry}**` : entry)),
  lang: 'fr-FR',
  title: 'Médiation numérique',
  description: 'Fonctionnement du site de formation à la médiation numérique : fonctionnalités, architecture, sécurité, tests et déploiement',
  base: '/docs/',
  cleanUrls: true,
  lastUpdated: true,
  markdown: { config: codeLinksToGithub, anchor: { slugify: githubSlug } },
  vite: {
    // Mermaid 11 importe fastdom (CommonJS) : en dev, il doit être pré-compilé pour fournir un export par défaut
    optimizeDeps: { include: ['mermaid > fastdom', 'mermaid > fastdom/extensions/fastdom-promised.js'] }
  },
  ignoreDeadLinks: [/^https?:\/\/localhost/],
  themeConfig: {
    nav: [
      { text: 'Fonctionnalités', link: '/fonctionnalites/' },
      { text: 'Architecture', link: '/architecture/' },
      { text: 'Tests', link: '/tests/' },
      { text: 'Déploiement', link: '/deploiement/' },
      { text: 'VAE', link: '/vae/' }
    ],
    sidebar,
    outline: { level: [2, 3], label: 'Sur cette page' },
    search: {
      provider: 'local',
      options: {
        translations: {
          button: { buttonText: 'Rechercher', buttonAriaLabel: 'Rechercher' },
          modal: { noResultsText: 'Aucun résultat pour', resetButtonTitle: 'Effacer', footer: { selectText: 'ouvrir', navigateText: 'naviguer', closeText: 'fermer' } }
        }
      }
    },
    socialLinks: [{ icon: 'github', link: GITHUB_REPO }],
    editLink: { pattern: `${GITHUB_REPO}/edit/main/docs/:path`, text: 'Modifier cette page' },
    docFooter: { prev: 'Page précédente', next: 'Page suivante' },
    lastUpdated: { text: 'Mis à jour' },
    darkModeSwitchLabel: 'Thème',
    sidebarMenuLabel: 'Menu',
    returnToTopLabel: 'Haut de page'
  },
  mermaid: { securityLevel: 'strict' }
}))
