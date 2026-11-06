// Préchargement du service worker, utilisé par vite.config.js au moment du build (Node, pas dans sw.js).
// Le site doit s'ouvrir hors ligne dès la première visite : la page /spa/ et tous les scripts et styles
// du build, y compris ceux des vues chargées à la demande, sont gardés à l'installation.
import { existsSync, readFileSync } from 'node:fs'
import { relative, resolve } from 'node:path'
import { SPA_SHELL_URL } from './spaNavigation.js'

// Fichiers jamais utiles hors ligne : export des diaporamas, démonstration des composants
const TOUJOURS_IGNORES = ['**/pptxgen*', '**/construirePptx*', '**/ComponentsDemo*']
// Module laboratoires désactivé : ses vues ne sont jamais ouvertes
const LABORATOIRES = ['**/Laboratoire*', '**/GrapheVueFlow*']

// Fichiers du build courant d'après le manifest de Vite. Le dossier n'est pas vidé entre deux builds
// (emptyOutDir: false) : sans ce filtre, les anciens fichiers seraient préchargés aussi.
export function fichiersDuBuild(manifest) {
  const fichiers = new Set()
  for (const entree of Object.values(manifest)) {
    fichiers.add(entree.file)
    for (const fichier of [...(entree.css ?? []), ...(entree.assets ?? [])]) fichiers.add(fichier)
  }
  return fichiers
}

/**
 * Options Workbox de préchargement. Les deux dossiers de sortie ont le même parent (public/) :
 * on y cherche les fichiers, puis on rend les adresses absolues, car sw.js vit dans /spa/.
 */
export function precacheOptions({ dirname, buildDir, pwaDir, laboratoires, revision }) {
  const racine = resolve(dirname, pwaDir, '..')
  const build = relative(racine, resolve(dirname, buildDir))
  const spa = relative(racine, resolve(dirname, pwaDir))
  const manifestVite = resolve(dirname, buildDir, '.vite/manifest.json')

  return {
    globDirectory: racine,
    globPatterns: [`${build}/assets/*.{js,css,woff2}`, `${spa}/*.png`],
    globIgnores: [
      `${spa}/screenshot.png`,
      ...TOUJOURS_IGNORES,
      ...(laboratoires ? [] : LABORATOIRES)
    ],
    modifyURLPrefix: { '': '/' },
    // Le script principal dépasse la limite par défaut de Workbox (2 Mo)
    maximumFileSizeToCacheInBytes: 5 * 1024 * 1024,
    additionalManifestEntries: [{ url: SPA_SHELL_URL, revision }],
    manifestTransforms: [async (entrees) => {
      if (!existsSync(manifestVite)) return { manifest: entrees }
      const actuels = fichiersDuBuild(JSON.parse(readFileSync(manifestVite, 'utf8')))
      const prefixe = `/${build}/`
      return {
        manifest: entrees.filter(({ url }) => !url.startsWith(prefixe) || actuels.has(url.slice(prefixe.length)))
      }
    }]
  }
}
