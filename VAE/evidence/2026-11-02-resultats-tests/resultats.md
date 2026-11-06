# Résultats partiels du plan de tests — 2026-11-02 (14 h 25 – 14 h 30)

Plan : ../2026-11-02-plan-de-tests/plan-de-tests.md (archivé à 14 h 24 avant exécution). Version : 30da5e5. Résultats partiels : session interrompue (limite d'usage).

| # | Résultat | Preuve |
|---|---|---|
| A1 | Conforme : projet Compose `vae-inclusion-iso`, 4 conteneurs, 13 migrations, schéma validé (14 h 26) | iso4-install.log |
| A2 | Conforme : `make check` exit 0 ; PHPStan sans erreur ; PHPUnit 88 tests / 320 assertions ; Vitest 28 fichiers / 191 tests ; build Vite | iso4-check.log |
| A3 | Non obtenu : `make docs-build` en échec réseau (`npm ci` ETIMEDOUT) à 14 h 01 et 14 h 13 ; réussi à 13 h 44 avant les pages de la phase 3 | journal |
| A4 | Conforme : run 41442887513, 6 jobs success | ../2026-11-02-cadrage/ci-main2.txt |
| A5, F7, F8 | Non exécutés | — |
| F1, F2, F3, F4 | Conformes (navigateur intégré, session administrateur existante du candidat : essai non anonyme) | — |
| F5, F9, F10 | Conformes | prod-http-scenarios.txt |
| F6 | Partiel : QCM démarré, uniquement des GET ; après « Réponse », choix masqués et seule une barre verte avec coche, sans texte ni explication (anomalie mineure). Bouton vide avant sélection, sans nom accessible (anomalie mineure) | — |
| U1 | Conforme : 390 px, scrollWidth 390, menu présent (Chrome DevTools, contexte anonyme) | — |
| U2 | Non exécuté | — |
| U3 | Conforme mais lent : Slow 3G, accueil FCP 13,4 s, chargement 22,8 s, 650 Ko ; cours ensuite 0,26 s | — |
| U4 | Conforme : réseau coupé (fetch non mis en cache en échec), cours affiché après rechargement | u4-cours-hors-ligne-390.png |
