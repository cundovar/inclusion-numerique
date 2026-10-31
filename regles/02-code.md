# Exemples de code

## COD-01 — Un langage par bloc

**Règle.** Chaque bloc de code indique son langage : `<pre><code class="language-php">…</code></pre>` (`php`, `javascript`, `vue`, `yaml`, `bash`, `sql`, `twig`, `json`…), pour la coloration et la lecture.

## COD-02 — Des exemples qui fonctionnent

**Règle.** Un exemple est complet ou dit clairement ce qui est omis (`// …`). Il respecte la version annoncée (Symfony 6.4, Vue 3, PHP 8.2…) et n'utilise pas d'API dépréciée sans le signaler.

**Dans un cours.** Les espaces de noms, les `use` et les imports nécessaires sont présents ; une commande indique d'où la lancer.

## COD-03 — Bonnes pratiques visibles

**Règle.** Les exemples montrent la pratique recommandée par la documentation officielle (typage, injection de dépendances, Composition API…). Une mauvaise pratique n'apparaît que pour être corrigée, et elle est signalée comme telle.
