import { describe, it, expect, beforeEach } from 'vitest'
import { mkdtempSync, mkdirSync, writeFileSync } from 'node:fs'
import { tmpdir } from 'node:os'
import { join } from 'node:path'
import { fichiersDuBuild, precacheOptions } from '../../../pwa/precache.js'
import { SPA_SHELL_URL } from '../../../pwa/spaNavigation.js'

const MANIFEST = {
  'src/main.js': { file: 'assets/main-A.js', css: ['assets/main-B.css'], assets: ['assets/font-C.woff2'] },
  'src/views/PageQCM.vue': { file: 'assets/PageQCM-D.js', css: ['assets/PageQCM-E.css'] }
}

let dirname

beforeEach(() => {
  // Arborescence du build : front/ et ../public/{build,spa}, comme en CI
  const racine = mkdtempSync(join(tmpdir(), 'precache-'))
  dirname = join(racine, 'front')
  mkdirSync(dirname)
  mkdirSync(join(racine, 'public/build/.vite'), { recursive: true })
  mkdirSync(join(racine, 'public/spa'))
  writeFileSync(join(racine, 'public/build/.vite/manifest.json'), JSON.stringify(MANIFEST))
})

const options = (laboratoires = false) => precacheOptions({
  dirname, buildDir: '../public/build', pwaDir: '../public/spa', laboratoires, revision: '7'
})

describe('service worker — préchargement', () => {
  it('liste tous les fichiers du build courant, vues chargées à la demande comprises', () => {
    expect([...fichiersDuBuild(MANIFEST)].sort()).toEqual([
      'assets/PageQCM-D.js', 'assets/PageQCM-E.css', 'assets/font-C.woff2', 'assets/main-A.js', 'assets/main-B.css'
    ])
  })

  it('cherche dans public/ et rend les adresses absolues (sw.js vit dans /spa/)', () => {
    const o = options()

    expect(o.globDirectory).toBe(join(dirname, '../public'))
    expect(o.globPatterns).toEqual(['build/assets/*.{js,css,woff2}', 'spa/*.png'])
    expect(o.modifyURLPrefix).toEqual({ '': '/' })
    expect(o.additionalManifestEntries).toEqual([{ url: SPA_SHELL_URL, revision: '7' }])
  })

  it('n’ajoute pas les vues du module laboratoires quand il est désactivé', () => {
    expect(options(false).globIgnores).toContain('**/Laboratoire*')
    expect(options(true).globIgnores).not.toContain('**/Laboratoire*')
    expect(options(true).globIgnores).toContain('**/pptxgen*')
  })

  it('écarte les fichiers d’un ancien build restés dans le dossier', async () => {
    const [transform] = options().manifestTransforms
    const entrees = ['/build/assets/main-A.js', '/build/assets/main-ANCIEN.js', '/spa/pwa.png']
      .map((url) => ({ url, revision: null, size: 1 }))

    const { manifest } = await transform(entrees)

    expect(manifest.map(({ url }) => url)).toEqual(['/build/assets/main-A.js', '/spa/pwa.png'])
  })
})
