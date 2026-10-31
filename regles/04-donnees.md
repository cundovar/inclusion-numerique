# Secrets et données personnelles

## DON-01 — Aucun secret ni donnée réelle

**Règle.** Un cours ou un QCM ne contient ni vraie clé d'API, ni mot de passe, ni jeton, ni adresse de courriel, téléphone, IBAN ou numéro de sécurité sociale réels, même dans un bloc de code.

**Dans un cours.**
- Clés et jetons écrits comme `sk-XXXX` ou lus depuis une variable d'environnement (`%env(API_KEY)%`, `process.env.API_KEY`).
- Courriels en `@example.com`, numéros écrits avec des X.
- Les fichiers `.env.local` et équivalents ne sont jamais montrés avec de vraies valeurs.
