# Décisions d'architecture

Chaque choix est présenté avec ce qu'il apporte et ce qu'il coûte. Le tableau de référence est dans [Architecture logicielle](../conception/architecture.md#6-choix-darchitecture-et-justification).

| Décision | Pourquoi | Contrepartie | État |
|---|---|---|---|
| Monolithe Symfony qui sert aussi la SPA | Un déploiement, un domaine, session par cookie sans CORS ; SEO calculé côté serveur | Front et back livrés ensemble | <Status is="prod" /> |
| API Platform pour les contenus | CRUD, pagination et filtres générés | Entités couplées au framework | <Status is="prod" /> |
| Hexagonal sur les contextes à règles métier | Règles testables sans base ; persistance remplaçable | Deux styles cohabitent | <Status is="prod" /> |
| API d'administration à clé pour les agents IA | Les agents passent par l'API au lieu d'écrire dans MySQL | Rotation des clés à organiser | <Status is="prod" /> |
| Connecteurs MCP intégrés à Symfony, autorisés par OAuth 2.1, copiés du gabarit des sites de formation (`app-neutre`, le plus complet : captures, diaporamas, contenus réservés) au lieu de l'ancien pont Node DeepSeek | Ajout depuis claude.ai ou ChatGPT sans clé à copier ; règles métier et écritures restent dans Symfony ; publication seulement après la relecture d'un autre connecteur | Serveur OAuth à maintenir ; l'API à clé reste en place tant que n8n l'utilise | <Status is="local" /> |
| PWA et IndexedDB | Lecture des cours sans réseau | Copies locales possiblement périmées | <Status is="prod" /> |
| Micro-service Node et MongoDB pour les résultats de QCM | Données de type document et agrégations ; l'hébergement mutualisé ne fait tourner ni Node ni MongoDB | Une seconde infrastructure à exploiter ; pas d'intégrité entre les deux bases | <Status is="prevu" /> |
| Docker en local, mutualisé en production | Environnement reproductible, hébergement peu coûteux | PHP 8.3 en local, 8.2 en production | <Status is="prod" /> |
| Documentation en VitePress à partir de `docs/` | Même outillage que le front (Vite, Vue) ; les documents restent à côté du code | Pages de fonctionnalité à écrire à la main | <Status is="local" /> |
| Second site (`main2`, inclusion-numerique) sur la base de mediateur | Afficher le contenu de mediateur avec le code DevDoc | Deux applications ecrivent dans les memes tables ; migrations additives seulement, historique et filtre de schema separes | <Status is="local" /> |
