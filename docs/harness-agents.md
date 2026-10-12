# Harness agents IA — Claude Code, Codex et AIDD

Architecture du harness du projet : ce que chaque agent charge, le socle partagé,
la boucle de validation, la méthode AIDD et le dossier VAE.

```mermaid
---
title: Harness agents IA du projet devdoc-local
---
flowchart LR
    subgraph Agents["Agents"]
        Claude["Claude Code"]
        Codex["Codex CLI"]
    end

    subgraph EntreeClaude["Entrées Claude Code"]
        ClaudeMd["CLAUDE.md (import @AGENTS.md)"]
        ClaudeSettings[".claude/settings.json"]
        ClaudePerms["Permissions allow / ask / deny"]
        ClaudeHooks["Hooks PreToolUse, PostToolUse, Stop"]
    end

    subgraph EntreeCodex["Entrées Codex"]
        CodexHooks[".codex/hooks.json"]
        CodexRules[".codex/rules/project.rules"]
    end

    subgraph Socle["Socle partagé"]
        subgraph Consignes["Consignes"]
            AgentsRoot["AGENTS.md racine"]
            AgentsSrc["src/AGENTS.md backend hexagonal"]
            AgentsFront["front/AGENTS.md Vue / Vite"]
        end
        subgraph Hooks[".agents/hooks"]
            HookLib["lib.sh : chemins Claude et apply_patch"]
            HookProtect["protect-files.sh : refuse .env, migrations commitées, VAE figé"]
            HookLint["lint-file.sh : php -l ou ESLint du fichier"]
            HookStop["stop-check.sh : exige make check"]
        end
        Marker[".agents/.needs-check"]
        subgraph Skills["Skills projet"]
            SkillVae[".claude/skills/vae-documentation-sync"]
            SkillLink[".agents/skills (lien symbolique)"]
        end
    end

    subgraph Validation["Validation"]
        MakeFast["make check-fast"]
        MakeCheck["make check"]
        subgraph Docker["Conteneurs Docker"]
            PhpContainer["php"]
            FrontContainer["front"]
        end
        LintPhp["Syntaxe PHP"]
        PhpStan["PHPStan niveau 5"]
        EsLint["ESLint"]
        PhpUnit["PHPUnit"]
        ViteBuild["Build Vite"]
    end

    subgraph Aidd["Méthode AIDD (skills globaux)"]
        AiddPlan["aidd-dev 01-plan"]
        AiddImplement["aidd-dev 02-implement"]
        AiddAssert["aidd-dev 03-assert"]
        AiddReview["aidd-dev 05-review"]
        AiddCommit["aidd-vcs 01-commit"]
        AiddTasks["aidd_docs/tasks : plans et phases"]
        AiddAssertions["aidd_docs/memory/coding-assertions.md"]
    end

    subgraph Vae["Dossier VAE"]
        VaePreuves["VAE/preuves.md"]
        VaeRapport["VAE/rapport-cda.md"]
        VaeJournal["VAE/journal.md"]
        VaeHistorique["VAE/result_analyse_VAE.md (figé)"]
    end

    Claude --> ClaudeMd
    Claude --> ClaudeSettings
    ClaudeSettings --> ClaudePerms
    ClaudeSettings --> ClaudeHooks
    ClaudeMd -- importe --> AgentsRoot
    Codex -- lit --> AgentsRoot
    Codex --> CodexHooks
    Codex --> CodexRules
    AgentsRoot --> AgentsSrc
    AgentsRoot --> AgentsFront

    ClaudeHooks --> HookProtect
    ClaudeHooks --> HookLint
    ClaudeHooks --> HookStop
    CodexHooks --> HookProtect
    CodexHooks --> HookLint
    CodexHooks --> HookStop
    HookProtect --> HookLib
    HookLint --> HookLib
    HookStop --> HookLib
    HookLint -- pose --> Marker
    HookStop -- lit --> Marker
    HookStop -- demande --> MakeCheck
    HookLint --> PhpContainer
    HookLint --> FrontContainer
    HookProtect -- protège --> VaeHistorique

    ClaudePerms -- autorise --> MakeCheck
    CodexRules -- autorise --> MakeCheck
    SkillLink --> SkillVae
    Claude --> SkillVae
    Codex --> SkillLink

    MakeCheck --> MakeFast
    MakeFast --> LintPhp
    MakeFast --> PhpStan
    MakeFast --> EsLint
    MakeCheck --> PhpUnit
    MakeCheck --> ViteBuild
    LintPhp --> PhpContainer
    PhpStan --> PhpContainer
    PhpUnit --> PhpContainer
    EsLint --> FrontContainer
    ViteBuild --> FrontContainer
    MakeCheck -- succès efface --> Marker

    Claude --> AiddPlan
    Codex --> AiddPlan
    AiddPlan --> AiddImplement
    AiddImplement --> AiddAssert
    AiddAssert --> AiddReview
    AiddReview --> AiddCommit
    AiddPlan -- écrit --> AiddTasks
    AiddAssert -- lit --> AiddAssertions
    AiddAssertions -- exécute --> MakeCheck
    AiddImplement -- déclenche --> HookLint

    MakeCheck -- résultats datés --> SkillVae
    SkillVae --> VaePreuves
    SkillVae --> VaeRapport
    SkillVae --> VaeJournal

    AgentsRoot -- règle « Documentation VitePress » --> DocsSite["Site VitePress docs/"]
    HookStop -- rappelle --> DocsSite
    SkillVae --> DocsSite
    DocsSite -- vérifié par --> DocsBuild["make docs-build (liens morts)"]
```

## Documentation à jour après chaque changement validé

`AGENTS.md` impose à Claude Code et à Codex de reporter dans le site VitePress toute modification validée par `make check` qui change ce que le site décrit (fonctionnalité, API, données, architecture, tests, CI, déploiement), selon une table « changement → page ». Le rappel de fin de tâche (`stop-check.sh`) et le skill `vae-documentation-sync` le redemandent ; `make docs-build` vérifie que le site compile sans lien mort.
