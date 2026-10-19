import { fileURLToPath } from 'node:url'
import fs from 'node:fs'
import path from 'node:path'
import { defineConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'

// Site VitePress à la racine de docs/ : les documents restent à côté du code
const SRC_DIR = fileURLToPath(new URL('..', import.meta.url)).replace(/\/$/, '')
const REPO_ROOT = path.resolve(SRC_DIR, '..')
const GITHUB_BLOB = 'https://github.com/cundovar/vue-symfony/blob/main/'

// Brouillons et anciennes notes de travail : hors du site, mais toujours lisibles sur GitHub
const EXCLUDED = ['application-plan/', 'test/', 'roadmap-100-hexagonal.md', 'hexagonal-audit.md',
  'architecture-hexagonale-option1.md', 'architecture-hexagonale-option2.md',
  'mcp-agents-cours-vers-symfony-checklist.md', 'filtrage-niveau-cours.md']

/**
 * Un lien qui pointe vers le code (hors de docs/) ou vers un dossier sans page
 * (ex. docs/conception/captures/), vers un fichier qui n'est pas une page (image liée)
 * ou vers une page exclue du site est réécrit vers GitHub au lieu de donner une page morte.
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
    text: 'Démarrer',
    items: [
      { text: 'Présentation', link: '/demarrer/' },
      { text: 'Lancer en local', link: '/demarrer/local' },
      { text: 'Glossaire', link: '/demarrer/glossaire' }
    ]
  },
  {
    text: 'Fonctionnalités',
    items: [
      { text: 'Vue d\'ensemble', link: '/fonctionnalites/' },
      { text: 'Cours', link: '/fonctionnalites/cours' },
      { text: 'Recherche', link: '/fonctionnalites/recherche' },
      { text: 'Exercices', link: '/fonctionnalites/exercices' },
      { text: 'QCM et progression', link: '/fonctionnalites/qcm' },
      { text: 'Hors ligne (PWA)', link: '/fonctionnalites/hors-ligne' },
      {
        text: 'Espace personnel',
        collapsed: false,
        items: [
          { text: 'Compte et connexion', link: '/fonctionnalites/compte' },
          { text: 'Notes chiffrées', link: '/fonctionnalites/notes' },
          { text: 'Favoris', link: '/fonctionnalites/favoris' },
          { text: 'Historique de visite', link: '/fonctionnalites/historique' },
          { text: 'Personnalisation', link: '/fonctionnalites/personnalisation' }
        ]
      },
      {
        text: 'Administration',
        collapsed: false,
        items: [
          { text: 'Back-office', link: '/fonctionnalites/back-office' },
          { text: 'Propositions de cours IA', link: '/fonctionnalites/propositions-ia' }
        ]
      }
    ]
  },
  {
    text: 'Architecture',
    collapsed: false,
    items: [
      { text: 'Architecture logicielle', link: '/conception/architecture' },
      { text: 'Audit hexagonal', link: '/final-hexagonal-audit' }
    ]
  },
  {
    text: 'Harness des agents IA',
    collapsed: false,
    items: [
      { text: 'Vue d\'ensemble', link: '/harness/' },
      { text: 'Consignes', link: '/harness/consignes' },
      { text: 'Permissions', link: '/harness/permissions' },
      { text: 'Hooks', link: '/harness/hooks' },
      { text: 'Validation', link: '/harness/validation' },
      { text: 'Méthode et documentation', link: '/harness/methode' },
      { text: 'Carte complète', link: '/harness-agents' }
    ]
  },
  {
    text: 'Données',
    items: [
      { text: 'Vue d\'ensemble', link: '/donnees/' },
      { text: 'MySQL, IndexedDB, MongoDB', link: '/conception/base-de-donnees' }
    ]
  },
  {
    text: 'API',
    items: [
      { text: 'Carte des API', link: '/api/' }
    ]
  },
  {
    text: 'Intégrations',
    items: [
      { text: 'Agents de cours (MCP)', link: '/migration-mcp-vers-symfony' }
    ]
  },
  {
    text: 'Qualité',
    items: [
      { text: 'Plan de tests', link: '/tests/plan-de-tests' },
      { text: 'CI/CD', link: '/CI_CD' }
    ]
  },
  {
    text: 'Projet',
    collapsed: true,
    items: [
      { text: 'Besoins', link: '/conception/besoins' },
      { text: 'Maquettes', link: '/conception/maquettes' },
      { text: 'Design system', link: '/conception/design-system' },
      { text: 'Gestion de projet', link: '/gestion-de-projet' },
      { text: 'Décisions d\'architecture', link: '/decisions/' }
    ]
  }
]

export default withMermaid(defineConfig({
  // Brouillons et anciennes notes de travail, hors du site
  srcExclude: EXCLUDED.map((entry) => (entry.endsWith('/') ? `${entry}**` : entry)),
  lang: 'fr-FR',
  title: 'DevDoc',
  description: 'Fonctionnement de DevDoc : fonctionnalités, architecture, données, API et exploitation',
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
      { text: 'Architecture', link: '/conception/architecture' },
      { text: 'Données', link: '/donnees/' },
      { text: 'API', link: '/api/' },
      { text: 'Qualité', link: '/tests/plan-de-tests' }
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
    socialLinks: [{ icon: 'github', link: 'https://github.com/cundovar/vue-symfony' }],
    editLink: { pattern: 'https://github.com/cundovar/vue-symfony/edit/main/docs/:path', text: 'Modifier cette page' },
    docFooter: { prev: 'Page précédente', next: 'Page suivante' },
    lastUpdated: { text: 'Mis à jour' },
    darkModeSwitchLabel: 'Thème',
    sidebarMenuLabel: 'Menu',
    returnToTopLabel: 'Haut de page'
  },
  mermaid: { securityLevel: 'strict' }
}))
