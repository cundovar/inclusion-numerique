# Harness des agents IA

Le code de DevDoc est en grande partie écrit avec des agents IA : **Claude Code** et **Codex**. Le *harness* est l'ensemble des fichiers qui encadrent ce travail, pour que les deux agents suivent les mêmes règles, ne puissent pas casser ce qui compte et ne déclarent pas une tâche terminée sans preuve.

| Garantie | Comment | Page |
|---|---|---|
| Les deux agents ont les **mêmes consignes** | Un seul fichier `AGENTS.md`, lu par Codex et importé par Claude Code | [Consignes](consignes.md) |
| Ils **ne peuvent pas détruire** la base, lire les secrets ou pousser seuls | Permissions Claude et règles Codex : autorisé, à confirmer, interdit | [Permissions](permissions.md) |
| Ils **ne peuvent pas modifier** les fichiers protégés | Hook avant écriture, commun aux deux agents | [Hooks](hooks.md) |
| Une erreur de syntaxe est **signalée tout de suite** | Hook après écriture : `php -l` ou ESLint dans Docker | [Hooks](hooks.md) |
| Une tâche **n'est pas finie** sans `make check` réussi | Marqueur posé à chaque modification, rappel en fin de tâche | [Validation](validation.md) |
| La **documentation suit** le code | Règle VitePress, skill de synchronisation VAE | [Méthode et documentation](methode.md) |

## Les trois couches

```mermaid
flowchart TB
  subgraph C1["1. Consignes : ce que l'agent doit faire"]
    A["AGENTS.md (+ src/, front/)"]
    M["aidd_docs/memory/coding-assertions.md"]
  end
  subgraph C2["2. Garde-fous : ce que l'agent ne peut pas faire"]
    P["Permissions Claude / règles Codex"]
    H1["protect-files.sh (avant écriture)"]
  end
  subgraph C3["3. Boucle de preuve : ce que l'agent doit démontrer"]
    H2["lint-file.sh (après écriture)"]
    H3["stop-check.sh (fin de tâche)"]
    K["make check"]
    D["Documentation VitePress et VAE"]
  end
  C1 --> C2 --> C3
  H2 -- "pose .needs-check" --> H3
  H3 -- "exige" --> K
  K -- "efface .needs-check" --> H3
  K --> D
```

## Une tâche, du début à la fin

```mermaid
sequenceDiagram
  actor U as Utilisateur
  participant A as Agent (Claude Code ou Codex)
  participant P as protect-files.sh
  participant L as lint-file.sh
  participant S as stop-check.sh
  participant K as make check
  U->>A: demande
  A->>A: lit AGENTS.md (+ src/ ou front/)
  A->>P: écriture d'un fichier
  alt fichier protégé (.env.local, migration commitée, VAE figé…)
    P-->>A: refus et raison
  else autorisé
    P-->>A: ok
    A->>L: fichier écrit
    L->>L: pose .agents/.needs-check, php -l ou ESLint
    L-->>A: erreurs éventuelles (bloquant)
  end
  A->>S: veut s'arrêter
  S-->>A: bloqué : lance make check
  A->>K: make check
  K-->>A: succès, marqueur effacé
  A->>A: doc VitePress (make docs-build), preuves VAE si besoin
  A-->>U: compte rendu avec les résultats réels
  Note over A,U: commit et push : toujours sur confirmation
```

## Claude Code et Codex côte à côte

| Pièce | Claude Code | Codex | Partagé |
|---|---|---|---|
| Consignes | `CLAUDE.md` → `@AGENTS.md` | `AGENTS.md` lu directement | `AGENTS.md`, `src/AGENTS.md`, `front/AGENTS.md` |
| Commandes | `.claude/settings.json` (`permissions`) | `.codex/rules/project.rules` | Mêmes décisions |
| Hooks | `.claude/settings.json` (`hooks`) | `.codex/hooks.json` | Scripts `.agents/hooks/` |
| Skills | `.claude/skills/` | `.agents/skills/` (lien symbolique) | `vae-documentation-sync` |

La carte détaillée de toutes les pièces est dans [Carte complète](../harness-agents.md).

## Limites

::: warning
- **Rien n'oblige à mettre la doc à jour** : la règle est rappelée, pas vérifiée. Seul `make docs-build` détecte un lien cassé.
- **Le rappel peut être ignoré** : `stop-check.sh` bloque une seule fois (il laisse passer si l'agent s'est déjà arrêté une fois après le rappel), et un agent peut répondre que la tâche est volontairement intermédiaire.
- Les hooks de lint ne vérifient pas les fichiers du micro-service Node ni la documentation : seul `make check` (ou `make docs-build`) les contrôle.
- Le harness protège le dépôt local ; la CI reste le contrôle indépendant, et PHPStan y est encore informatif.
:::
