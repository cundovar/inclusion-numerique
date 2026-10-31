<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Entity\Category;
use App\Service\OAuth\PublicUrl;
use App\Service\Redaction\AccesPersonnaliseService;
use App\Service\Redaction\DiaporamaChecker;
use App\Service\Redaction\DiaporamaService;
use App\Service\Redaction\QcmDraftChecker;
use App\Service\Redaction\QcmDraftService;
use App\Service\Redaction\RedactionError;
use App\Service\Redaction\RedactionService;
use App\Service\Redaction\Regles;
use Mcp\Exception\ToolCallException;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Prompt;
use Mcp\Schema\PromptArgument;
use Mcp\Schema\ResourceDefinition;
use Mcp\Schema\Result\CallToolResult;
use Mcp\Schema\Tool;
use Mcp\Schema\ToolAnnotations;
use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Serveur MCP du connecteur de rédaction : mêmes outils, consignes et ressources pour claude.ai et ChatGPT.
 * Il prépare des cours et des QCM et les publie après une relecture favorable d'un autre connecteur. `$agent` identifie le connecteur qui appelle.
 */
final class RedactionMcpServer
{
    public const NAME = 'devdoc-redaction';
    public const VERSION = '0.10.0';

    public const INSTRUCTIONS = <<<'TXT'
        Assistant de rédaction de DevDoc, plateforme de cours Symfony, Vue et développement web.
        Vous préparez des cours en brouillon, puis vous les publiez avec publier_cours.
        Pour modifier un cours déjà publié, lisez lister_cours_publies puis voir_brouillon avec son identifiant,
        et utilisez modifier_brouillon : les changements sont immédiatement visibles sur le site.
        Pour retirer du site un cours, un module ou un parcours à la demande de la personne : mettre_en_brouillon.
        Avant d'écrire, lisez les règles (lire_regles) et le gabarit (obtenir_modele).
        Un brouillon doit être relu par un AUTRE connecteur (par exemple ChatGPT pour un brouillon rédigé
        avec Claude) avec la consigne « relire_brouillon » : la publication demande aucun contrôle bloquant et un
        avis favorable. Toute modification efface la relecture ; celle d'un cours publié est visible tout de suite.
        Pour un QCM : consigne « rediger_qcm », outils *_qcm, relecture par l'autre connecteur avec « relire_qcm »,
        puis publier_qcm. Si le catalogue n'a pas de thème ou de niveau adapté, créez-le avec creer_theme_qcm
        ou creer_niveau_qcm (après accord de la personne), puis relisez le catalogue avant de créer le QCM.
        Un QCM publié rejoint la série publique de son thème et de son niveau.
        Pour un diaporama de cours ou de chapitre (module) : consigne « rediger_diaporama », preparer_diaporama pour
        lire la source, puis creer_diaporama. C'est une synthèse pour projeter, jamais une copie du cours (DIA-02) ;
        le site applique son thème, l'agent ne choisit pas le design. Relecture par l'autre connecteur avec
        « relire_diaporama », puis publier_diaporama. Un chapitre attend que tous ses cours soient publiés.
        Cours et QCM personnalisés : un cours, un parcours ou un QCM peut être réservé à certains apprenants
        (comptes du site, lister_apprenants). Passez apprenantIds à creer_brouillon ou à publier_qcm, ou utilisez
        attribuer_acces ; le contenu n'est alors visible que par eux, et les règles et la relecture restent les mêmes.
        N'écrivez jamais dans un contenu personnalisé de données réelles sur la personne (nom, adresse, numéros) :
        le pseudo suffit pour le lui attribuer.
        TXT;

    private const ID = ['type' => 'integer', 'minimum' => 1];
    private const DATE = ['type' => 'string', 'pattern' => '^\d{4}-\d{2}-\d{2}$'];
    private const RULE_ID = ['type' => 'string', 'pattern' => '^[A-Z]{3}-\d{2}$'];

    public function __construct(
        private RedactionService $redaction,
        private QcmDraftService $qcmDrafts,
        private DiaporamaService $diaporamas,
        private AccesPersonnaliseService $acces,
        private Regles $regles,
        private LoggerInterface $logger,
        private PublicUrl $publicUrl,
        private RequestStack $requestStack,
        #[Autowire('%kernel.project_dir%/var/mcp-sessions')] private string $sessionDir,
    ) {}

    public function build(string $agent): Server
    {
        $builder = Server::builder()
            ->setServerInfo(self::NAME, self::VERSION, 'Préparer des brouillons de cours et de QCM conformes aux règles éditoriales du site.')
            ->setInstructions(self::INSTRUCTIONS)
            ->setSession(new FileSessionStore($this->sessionDir))
            ->setLogger($this->logger);

        foreach ($this->tools($agent) as [$tool, $callback]) {
            $builder->add($tool, new CallbackTool(fn (array $args) => $this->call($callback, $args)));
        }

        $builder->add(
            new Prompt('rediger_cours', 'Rédiger un cours', 'Démarche complète pour préparer un brouillon conforme aux règles de rédaction de DevDoc.', [
                new PromptArgument('sujet', 'Sujet du cours, par exemple « créer un premier contrôleur Symfony »', true),
                new PromptArgument('niveau', 'Niveau visé, par exemple débutant, intermédiaire ou confirmé', false),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::writingPrompt((string) ($args['sujet'] ?? ''), (string) ($args['niveau'] ?? ''))]),
        );
        $builder->add(
            new Prompt('relire_brouillon', 'Relire un brouillon', "Consigne de l'agent relecteur : justesse technique et règles de rédaction, sans modifier le contenu.", [
                new PromptArgument('id', 'Identifiant du brouillon', true),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::reviewPrompt((int) ($args['id'] ?? 0))]),
        );

        $builder->add(
            new Prompt('rediger_qcm', 'Rédiger un QCM', "Démarche pour préparer un QCM (question, réponses, correction) et le faire relire avant publication.", [
                new PromptArgument('sujet', 'Notion à vérifier, par exemple « le cycle de vie d\'un composant Vue »', true),
                new PromptArgument('niveau', 'Niveau visé, par exemple débutant, intermédiaire ou confirmé', false),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::qcmWritingPrompt((string) ($args['sujet'] ?? ''), (string) ($args['niveau'] ?? ''))]),
        );
        $builder->add(
            new Prompt('relire_qcm', 'Relire un QCM', "Consigne de l'agent relecteur d'un QCM : justesse, réponses fausses, correction et clarté, sans modifier le QCM.", [
                new PromptArgument('id', 'Identifiant du brouillon de QCM', true),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::qcmReviewPrompt((int) ($args['id'] ?? 0))]),
        );

        $builder->add(
            new Prompt('rediger_diaporama', 'Rédiger un diaporama', 'Démarche pour synthétiser un cours ou un chapitre en diaporama prêt à projeter, puis le faire relire avant publication.', [
                new PromptArgument('cible', 'cours (un cours) ou module (un chapitre)', true),
                new PromptArgument('id', 'Identifiant du cours ou du module (lister_cours_publies, lister_catalogue)', true),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::diaporamaWritingPrompt(($args['cible'] ?? '') === 'module' ? 'module' : 'cours', (int) ($args['id'] ?? 0))]),
        );
        $builder->add(
            new Prompt('relire_diaporama', 'Relire un diaporama', "Consigne de l'agent relecteur d'un diaporama : fidélité au cours, synthèse, notes et règles DIA, sans modifier le diaporama.", [
                new PromptArgument('id', 'Identifiant du diaporama', true),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::diaporamaReviewPrompt((int) ($args['id'] ?? 0))]),
        );

        foreach ($this->regles->files() as $file => $text) {
            $builder->add(
                new ResourceDefinition('regles:///'.$file, 'regles-'.basename($file, '.md'), 'Règles — '.$file, 'Référentiel éditorial du site (regles/)', 'text/markdown'),
                new CallbackResource(static fn () => $text),
            );
        }

        return $builder->build();
    }

    /**
     * @return list<array{0: Tool, 1: \Closure(array<string, mixed>): mixed}>
     */
    private function tools(string $agent): array
    {
        $readOnly = new ToolAnnotations(readOnlyHint: true, openWorldHint: false);
        $write = new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: false);
        $learnerIds = ['type' => 'array', 'items' => self::ID, 'minItems' => 1, 'maxItems' => 200, 'description' => 'Apprenants destinataires (lister_apprenants)'];
        $contentType = ['type' => 'string', 'enum' => array_keys(AccesPersonnaliseService::TYPES), 'description' => 'cours (identifiant de cours), parcours (lister_catalogue) ou qcm (identifiant du QCM publié, pas du brouillon)'];
        $draftFields = [
            'titre' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'parcoursId' => self::ID + ['description' => 'Identifiant du parcours (lister_catalogue)'],
            'moduleId' => ['type' => ['integer', 'null'], 'minimum' => 1],
            'niveauId' => ['type' => ['integer', 'null'], 'minimum' => 1],
            'contenu' => ['type' => 'string', 'maxLength' => 200000, 'description' => 'HTML du cours, selon le gabarit ; blocs de code en <pre><code class="language-…"> ; captures par marqueur [capture:ID] sur une ligne seule'],
            'sourceOfficielle' => ['type' => ['string', 'null'], 'format' => 'uri', 'description' => 'Page de documentation officielle de référence (symfony.com, vuejs.org…)'],
            'verifieLe' => ['type' => ['string', 'null'], 'pattern' => self::DATE['pattern'], 'description' => 'Date de vérification auprès de la source, AAAA-MM-JJ'],
        ];

        return [
            [
                new Tool('lister_catalogue', 'Lister le catalogue', self::schema([]), 'Rubriques (grands thèmes), parcours (avec leur rubrique), modules, niveaux, avec leurs identifiants. Organisation : rubrique → parcours → modules → cours.', $readOnly),
                fn () => $this->redaction->catalogue(),
            ],
            [
                new Tool('creer_parcours', 'Créer un parcours', self::schema([
                    'nom' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255, 'description' => 'Par exemple « Symfony »'],
                    'description' => ['type' => ['string', 'null'], 'maxLength' => 2000],
                    'niveauId' => ['type' => ['integer', 'null'], 'minimum' => 1],
                    'rubriqueId' => ['type' => ['integer', 'null'], 'minimum' => 1, 'description' => 'Rubrique qui regroupe ce parcours (lister_catalogue, creer_rubrique)'],
                ], ['nom']), "Ajoute un parcours au catalogue. Il n'apparaît sur le site qu'une fois un de ses cours publié. À créer seulement à la demande de la personne.", $write),
                fn (array $args) => $this->redaction->createParcours($args),
            ],
            [
                new Tool('creer_rubrique', 'Créer une rubrique', self::schema([
                    'nom' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255, 'description' => "Grand thème qui regroupe des parcours, par exemple « Back-end »"],
                    'ordre' => ['type' => 'integer', 'minimum' => 0, 'description' => "Rang sur l'accueil ; par défaut après les rubriques existantes"],
                ], ['nom']), "Ajoute une grande rubrique au-dessus des parcours (rubrique → parcours → modules → cours). Rien n'est publié. Range ensuite les parcours avec classer_parcours. À créer seulement à la demande de la personne.", $write),
                fn (array $args) => $this->redaction->createRubrique($args),
            ],
            [
                new Tool('ordonner_rubriques', 'Ordonner les rubriques', self::schema([
                    'rubriqueIds' => ['type' => 'array', 'items' => self::ID, 'minItems' => 1, 'maxItems' => 50, 'description' => "Rubriques dans l'ordre où elles doivent apparaître : le premier identifiant devient le rang 1 (lister_catalogue)"],
                ], ['rubriqueIds']), "Fixe l'ordre des grandes rubriques sur l'accueil et dans le menu du site, d'après l'ordre des identifiants (rang 1, 2, 3…). Les rubriques non citées passent après. Ne modifie ni les parcours ni les cours. Renvoie le rang de chaque rubrique et les identifiants refusés.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->orderRubriques(array_map('intval', (array) $args['rubriqueIds'])),
            ],
            [
                new Tool('classer_parcours', 'Classer des parcours dans une rubrique', self::schema([
                    'parcoursIds' => ['type' => 'array', 'items' => self::ID, 'minItems' => 1, 'maxItems' => 50, 'description' => 'Parcours à ranger (lister_catalogue)'],
                    'rubriqueId' => ['type' => ['integer', 'null'], 'minimum' => 1, 'description' => 'Rubrique de destination ; null pour retirer les parcours de leur rubrique'],
                ], ['parcoursIds', 'rubriqueId']), "Range des parcours dans une rubrique, ou les déplace d'une rubrique à l'autre. Ne modifie ni les cours, ni les modules, ni le rang des parcours (ordonner_parcours). Renvoie les parcours classés (ancienne et nouvelle rubrique) et les refus.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->classifyParcours(array_map('intval', (array) $args['parcoursIds']), isset($args['rubriqueId']) ? (int) $args['rubriqueId'] : null),
            ],
            [
                new Tool('creer_module', 'Créer un module', self::schema([
                    'nom' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
                    'parcoursId' => self::ID + ['description' => 'Parcours qui contient le module (lister_catalogue)'],
                    'niveauId' => ['type' => ['integer', 'null'], 'minimum' => 1],
                ], ['nom', 'parcoursId']), "Ajoute un module (regroupement de cours) dans un parcours. Rien n'est publié.", $write),
                fn (array $args) => $this->redaction->createModule($args),
            ],
            [
                new Tool('creer_niveau', 'Créer un niveau', self::schema([
                    'nom' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255, 'description' => 'Par exemple « Débutant »'],
                    'ordre' => ['type' => 'integer', 'minimum' => 0, 'description' => 'Rang du niveau ; par défaut après les niveaux existants'],
                ], ['nom']), "Ajoute un niveau de cours. Rien n'est publié.", $write),
                fn (array $args) => $this->redaction->createNiveau($args),
            ],
            [
                new Tool('lire_regles', 'Lire les règles éditoriales du site', self::schema([
                    'identifiants' => ['type' => 'array', 'items' => self::RULE_ID, 'maxItems' => 40, 'description' => 'Identifiants de règles, par exemple ["RED-01", "COD-01"]'],
                    'fichier' => ['type' => 'string', 'description' => "Nom d'un fichier de regles/, par exemple controles.md"],
                ]), "Sans argument : statut du référentiel et liste des règles. Avec des identifiants (RED-01, COD-01…) : texte complet de ces règles et leurs sources. Avec 'fichier' : un fichier entier (README.md, sources.md, controles.md…).", $readOnly),
                fn (array $args) => $this->readRules($args),
            ],
            [
                new Tool('obtenir_modele', 'Obtenir un gabarit de cours', self::schema([
                    'modele' => ['type' => 'string', 'enum' => ['cours']],
                ], ['modele']), 'Gabarit HTML à suivre pour un cours DevDoc.', $readOnly),
                fn (array $args) => $this->regles->modeles()[(string) $args['modele']] ?? 'Gabarit introuvable dans regles/modeles/.',
            ],
            [
                new Tool('lister_cours_publies', 'Lister les cours publiés', self::schema([]), 'Cours déjà visibles sur le site, avec leurs identifiants et adresses (slugs). Pour corriger un cours, ouvrez-le avec voir_brouillon puis utilisez modifier_brouillon ; la modification est immédiate.', $readOnly),
                fn () => $this->redaction->listPublishedCourses(),
            ],
            [
                new Tool('lister_brouillons', 'Lister les brouillons', self::schema([]), 'Cours en brouillon, en revue ou à réviser, avec leur auteur.', $readOnly),
                fn () => $this->redaction->listDrafts(),
            ],
            [
                new Tool('voir_brouillon', 'Voir un brouillon', self::schema(['id' => self::ID], ['id']), "Contenu complet d'un cours, avec sa relecture éventuelle.", $readOnly),
                fn (array $args) => $this->redaction->showDraft((int) $args['id']),
            ],
            [
                new Tool('creer_brouillon', 'Créer un brouillon', self::schema($draftFields + [
                    'slug' => ['type' => 'string', 'pattern' => '^[a-z0-9]+(-[a-z0-9]+)*$', 'maxLength' => 90, 'description' => 'Adresse du cours, par exemple symfony-premier-controleur'],
                    'apprenantIds' => $learnerIds + ['description' => 'Cours personnalisé : apprenants à qui le cours sera réservé une fois publié (lister_apprenants). Sans ce champ, le cours sera public'],
                ], ['titre', 'slug', 'parcoursId', 'contenu']), 'Crée un cours en brouillon (invisible sur le site). Le statut est toujours fixé par le site. Avec apprenantIds, le cours est réservé à ces apprenants dès sa création.', $write),
                fn (array $args) => $this->createReserved('cours', $args, fn (array $data) => $this->redaction->createDraft($data, $agent)),
            ],
            [
                new Tool('modifier_brouillon', 'Modifier un brouillon', self::schema(['id' => self::ID] + $draftFields, ['id']), 'Modifie un brouillon, un cours à réviser ou un cours publié (la modification est alors visible tout de suite sur le site). Toute modification efface la relecture.', $write),
                fn (array $args) => $this->redaction->updateDraft((int) $args['id'], array_diff_key($args, ['id' => true])),
            ],
            [
                new Tool('verifier_brouillon', 'Vérifier un brouillon', self::schema(['id' => self::ID], ['id']), 'Lance les contrôles automatiques du site (regles/controles.md) et rappelle le titre des règles concernées. Ne modifie rien.', $readOnly),
                fn (array $args) => $this->withRuleTitles($this->redaction->checkDraft((int) $args['id'])),
            ],
            [
                new Tool('enregistrer_relecture', 'Enregistrer une relecture', self::schema([
                    'id' => self::ID,
                    'avis' => ['type' => 'string', 'enum' => ['favorable', 'reserves', 'defavorable']],
                    'resume' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 2000],
                    'points' => ['type' => 'array', 'maxItems' => 50, 'items' => self::schema([
                        'regle' => ['type' => ['string', 'null'], 'pattern' => self::RULE_ID['pattern'], 'description' => 'Identifiant de la règle concernée'],
                        'constat' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000],
                        'suggestion' => ['type' => ['string', 'null'], 'maxLength' => 1000],
                    ], ['regle', 'constat'])],
                ], ['id', 'avis', 'resume', 'points']), "Réservé au connecteur relecteur, différent de celui qui a rédigé le brouillon. Enregistre l'avis (favorable, reserves, defavorable) et les constats rattachés aux règles. Ne modifie pas le contenu.", $write),
                fn (array $args) => $this->redaction->reviewDraft((int) $args['id'], array_diff_key($args, ['id' => true]), $agent),
            ],
            [
                new Tool('publier_cours', 'Publier un cours', self::schema([
                    'id' => self::ID,
                    'sansModule' => ['type' => 'boolean', 'default' => false, 'description' => 'true seulement si la personne a choisi de publier ce cours hors de tout module'],
                ], ['id']), "Publie le cours sur le site si les contrôles bloquants passent, si un autre connecteur l'a relu avec un avis favorable et si le cours est rangé dans un module (sauf sansModule). Sinon, renvoie ce qui bloque.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: true)),
                fn (array $args) => $this->redaction->publishDraft((int) $args['id'], (bool) ($args['sansModule'] ?? false)),
            ],
            [
                new Tool('mettre_en_brouillon', 'Retirer du site (remettre en brouillon)', self::schema([
                    'type' => ['type' => 'string', 'enum' => ['cours', 'module', 'parcours']],
                    'id' => self::ID + ['description' => 'Identifiant du cours (lister_cours_publies), du module ou du parcours (lister_catalogue)'],
                ], ['type', 'id']), "Retire immédiatement du site un cours publié, ou tous les cours publiés d'un module ou d'un parcours, en les repassant en brouillon ; un module ou un parcours sans cours publié disparaît du site. Rien n'est supprimé : contenu et relecture sont gardés. À faire seulement à la demande de la personne. Pour remettre en ligne : publier_cours sur chaque cours (une modification demande une nouvelle relecture).", new ToolAnnotations(readOnlyHint: false, destructiveHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->unpublish((string) $args['type'], (int) $args['id']),
            ],
            [
                new Tool('classer_cours', 'Classer des cours dans un module', self::schema([
                    'coursIds' => ['type' => 'array', 'items' => self::ID, 'minItems' => 1, 'maxItems' => 100, 'description' => "Cours à ranger, quel que soit leur statut (publiés compris), dans l'ordre où ils doivent apparaître dans le module : le premier identifiant devient le rang 1"],
                    'moduleId' => ['type' => ['integer', 'null'], 'minimum' => 1, 'description' => 'Module de destination, du même parcours que les cours (lister_catalogue) ; null pour retirer les cours de leur module'],
                ], ['coursIds', 'moduleId']), "Classement éditorial : range plusieurs cours dans un module ou les déplace d'un module à l'autre, et fixe leur ordre d'affichage d'après l'ordre des identifiants (rang 1, 2, 3…), y compris pour ceux déjà dans le module. Ne modifie ni le contenu, ni le statut, ni la relecture. Renvoie les cours classés (ancien et nouveau module, rang), ceux déjà dans ce module, le rang de chacun (ordres) et les refus avec leur raison.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->classifyCourses(array_map('intval', (array) $args['coursIds']), isset($args['moduleId']) ? (int) $args['moduleId'] : null),
            ],
            [
                new Tool('ordonner_parcours', "Ordonner les parcours du catalogue", self::schema([
                    'parcoursIds' => ['type' => 'array', 'items' => self::ID, 'minItems' => 1, 'maxItems' => 50, 'description' => "Parcours dans l'ordre où ils doivent apparaître : le premier identifiant devient le rang 1 (lister_catalogue)"],
                ], ['parcoursIds']), "Fixe l'ordre d'affichage des parcours dans le catalogue de leur public, d'après l'ordre des identifiants (rang 1, 2, 3…). Les parcours non cités passent après, par ordre alphabétique. Ne modifie ni les cours ni les modules. Renvoie le rang de chaque parcours et les identifiants refusés.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->orderParcours(array_map('intval', (array) $args['parcoursIds'])),
            ],
            [
                new Tool('definir_illustration_parcours', "Définir la miniature d'un parcours", self::schema([
                    'parcoursId' => self::ID + ['description' => 'Parcours concerné (lister_catalogue)'],
                    'illustrationId' => ['type' => ['integer', 'null'], 'minimum' => 1, 'description' => "Identifiant de l'illustration (celui du marqueur [capture:ID] renvoyé par le dépôt, ou lister_captures) ; null pour retirer la miniature"],
                ], ['parcoursId', 'illustrationId']), "Associe une illustration existante à un parcours : elle s'affiche en miniature sur sa carte du catalogue. Seule une image de type illustration est acceptée (jamais une capture de page de service) ; elle suit les mêmes règles que les autres images (texte alternatif, aucune donnée réelle). Pour une nouvelle image : preparer_depot_image avec le type illustration, envoi du fichier, puis cet outil avec l'identifiant renvoyé. Image carrée ou proche du carré conseillée ; elle est affichée en petit format sur la carte. Ne modifie ni les cours, ni les modules, ni le rang du parcours.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->setParcoursIllustration((int) $args['parcoursId'], isset($args['illustrationId']) ? (int) $args['illustrationId'] : null),
            ],
            [
                new Tool('remplacer_capture', "Remplacer une image dans un cours", self::schema([
                    'coursId' => self::ID,
                    'ancienneCaptureId' => self::ID + ['description' => 'Capture actuellement dans le cours (marqueur [capture:ID])'],
                    'nouvelleCaptureId' => self::ID + ['description' => 'Capture à mettre à la place (lister_captures, capturer_page ou preparer_depot_image)'],
                ], ['coursId', 'ancienneCaptureId', 'nouvelleCaptureId']), "Remplace partout dans un cours, même publié, le marqueur d'une capture par celui d'une autre. Le texte ne change pas et la relecture est gardée. Refusé si le cours ne contient pas l'ancienne capture, s'il est en relecture humaine ou si le remplacement ajoute un contrôle bloquant (capture sans source, par exemple). Vérifiez avant que la nouvelle image montre bien les repères cités dans le texte.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: true)),
                fn (array $args) => $this->redaction->replaceCapture((int) $args['coursId'], (int) $args['ancienneCaptureId'], (int) $args['nouvelleCaptureId']),
            ],
            [
                new Tool('soumettre_en_revue', 'Soumettre à la relecture humaine', self::schema(['id' => self::ID], ['id']), "Facultatif, à la place de publier_cours : passe le brouillon « En revue » pour qu'une personne le relise et le publie dans le back-office. Mêmes conditions, avec une relecture non défavorable.", $write),
                fn (array $args) => $this->redaction->submitDraft((int) $args['id']),
            ],
            [
                new Tool('lister_captures', 'Lister les captures', self::schema([
                    'service' => ['type' => 'string', 'maxLength' => 100],
                ]), 'Captures existantes (marqueur, service, source, date, légende) pour les réutiliser.', $readOnly),
                fn (array $args) => $this->redaction->listCaptures(isset($args['service']) ? (string) $args['service'] : null),
            ],
            [
                new Tool('capturer_page', 'Capturer une page officielle', self::schema([
                    'url' => ['type' => 'string', 'format' => 'uri', 'description' => 'Page publique officielle, par exemple https://symfony.com/doc/current/setup.html'],
                    'format' => ['type' => 'string', 'enum' => ['ordinateur', 'mobile'], 'default' => 'ordinateur'],
                    'elements' => ['type' => 'array', 'maxItems' => 9, 'description' => "Éléments à repérer, dans l'ordre des étapes", 'items' => self::schema([
                        'repere' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 9, 'description' => 'Numéro du repère, repris dans le texte du cours'],
                        'texte' => ['type' => 'string', 'maxLength' => 120, 'description' => "Texte visible de l'élément, par exemple « Getting Started »"],
                        'selecteur' => ['type' => 'string', 'maxLength' => 200, 'description' => 'Ou un sélecteur CSS'],
                    ], ['repere'])],
                    'service' => ['type' => 'string', 'maxLength' => 100],
                    'legende' => ['type' => 'string', 'minLength' => 1, 'description' => 'Ce que la personne doit repérer, en citant les numéros des repères'],
                    'texteAlternatif' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500, 'description' => "Description de l'image et de la position des repères"],
                    'aRevoirLe' => self::DATE + ['description' => 'Par défaut dans 3 mois'],
                ], ['url', 'legende', 'texteAlternatif']), "Le site ouvre toute page publique en https et en fait la capture, avec des repères numérotés sur les éléments à montrer (CAP-07). Aucun domaine prédéfini : toute adresse publique est possible. Les adresses locales ou privées sont refusées pour protéger le serveur. Jamais d'écran de connexion (refusé : utilisez alors une illustration fictive, CAP-03) ; aucune requête autre que GET et les cookies sont refusés. Une protection anti-robots n'est jamais contournée (CAP-08) : la capture se fait alors à la main dans le back-office. Renvoie le marqueur [capture:ID].", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: true)),
                fn (array $args) => $this->redaction->captureAutomatically($args + ['format' => 'ordinateur', 'elements' => []], $agent),
            ],
            [
                new Tool('preparer_depot_image', "Préparer le dépôt d'une image", self::schema([
                    'type' => ['type' => 'string', 'enum' => ['capture', 'illustration'], 'description' => "capture : page publique d'un site officiel (pageSource et dateCapture obligatoires) ; illustration : image fictive ou pédagogique (schéma, photo d'un objet)"],
                    'legende' => ['type' => 'string', 'minLength' => 1, 'description' => 'Ce que la personne doit repérer, en citant les repères éventuels'],
                    'texteAlternatif' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500, 'description' => "Description de l'image pour qui ne la voit pas"],
                    'service' => ['type' => ['string', 'null'], 'maxLength' => 100],
                    'pageSource' => ['type' => ['string', 'null'], 'format' => 'uri', 'description' => 'Page officielle capturée (type capture)'],
                    'dateCapture' => ['type' => ['string', 'null'], 'pattern' => self::DATE['pattern'], 'description' => 'Date de la capture, AAAA-MM-JJ (type capture)'],
                    'aRevoirLe' => self::DATE + ['description' => 'Par défaut dans 3 mois'],
                ], ['type', 'legende', 'texteAlternatif']), "Pour une image présente sur l'ordinateur de la personne. Vérifie la fiche de l'image et renvoie une adresse de dépôt à usage unique, valable 10 minutes, et la commande curl pour y envoyer le fichier. Seul un agent qui a accès aux fichiers de l'ordinateur (Claude Code, par exemple) peut faire l'envoi ; la réponse de l'envoi donne le marqueur [capture:ID] (pour la miniature d'un parcours, type illustration puis definir_illustration_parcours). Mêmes règles que les captures (CAP-01, CAP-03 : jamais de données réelles d'un usager).", $write),
                fn (array $args) => $this->redaction->prepareImageDeposit($args, $agent, $this->publicUrl->base($this->requestStack->getCurrentRequest() ?? Request::createFromGlobals())),
            ],
            ...$this->qcmTools($agent, $readOnly, $write, $learnerIds),
            ...$this->diaporamaTools($agent, $readOnly, $write),
            [
                new Tool('lister_apprenants', 'Lister les apprenants', self::schema([]), "Comptes apprenants du site (identifiant et pseudo seulement) et nombre de contenus qui leur sont réservés. Les administrateurs n'y figurent pas.", $readOnly),
                fn () => $this->acces->listLearners(),
            ],
            [
                new Tool('lister_acces', 'Lister les contenus personnalisés', self::schema([
                    'apprenantId' => self::ID + ['description' => "Seulement les contenus réservés à cet apprenant"],
                ]), 'Cours, parcours et QCM réservés, avec leurs destinataires.', $readOnly),
                fn (array $args) => $this->acces->listReserved(isset($args['apprenantId']) ? (int) $args['apprenantId'] : null),
            ],
            [
                new Tool('attribuer_acces', 'Réserver un contenu à des apprenants', self::schema([
                    'type' => $contentType,
                    'id' => self::ID,
                    'apprenantIds' => $learnerIds,
                ], ['type', 'id', 'apprenantIds']), "Réserve un cours, un parcours ou un QCM à ces apprenants, en plus de ses destinataires actuels. Un contenu public devient alors invisible pour tous les autres (un parcours réservé masque aussi ses modules et ses cours). Le statut et la relecture ne changent pas : un brouillon reste à publier.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->acces->grant((string) $args['type'], (int) $args['id'], array_map('intval', (array) $args['apprenantIds'])),
            ],
            [
                new Tool('retirer_acces', "Retirer l'accès d'apprenants", self::schema([
                    'type' => $contentType,
                    'id' => self::ID,
                    'apprenantIds' => $learnerIds,
                ], ['type', 'id', 'apprenantIds']), "Retire ces apprenants des destinataires. Le contenu reste réservé, même sans destinataire (il n'est alors visible par personne) : pour le rendre public, utilisez ouvrir_a_tous.", new ToolAnnotations(readOnlyHint: false, destructiveHint: true, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->acces->revoke((string) $args['type'], (int) $args['id'], array_map('intval', (array) $args['apprenantIds'])),
            ],
            [
                new Tool('ouvrir_a_tous', 'Rendre un contenu personnalisé public', self::schema([
                    'type' => $contentType,
                    'id' => self::ID,
                ], ['type', 'id']), "Retire la réservation et tous les destinataires : le contenu devient visible par tout le monde s'il est publié. À utiliser seulement à la demande de la personne, après avoir vérifié qu'il ne contient rien de propre à un apprenant.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->acces->openToAll((string) $args['type'], (int) $args['id']),
            ],
        ];
    }

    /**
     * Outils QCM : leurs identifiants de brouillon sont propres aux QCM, distincts de ceux des cours.
     *
     * @return list<array{0: Tool, 1: \Closure(array<string, mixed>): mixed}>
     */
    /**
     * @param array<string, mixed> $learnerIds schéma des destinataires d'un contenu personnalisé
     */
    private function qcmTools(string $agent, ToolAnnotations $readOnly, ToolAnnotations $write, array $learnerIds): array
    {
        $text = ['type' => 'string', 'minLength' => 1, 'maxLength' => QcmDraftChecker::MAX_TEXT_LENGTH];
        $qcmFields = [
            'question' => $text + ['description' => 'Une seule question, courte (QCM-03)'],
            'explication' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 5000, 'description' => 'Pourquoi la bonne réponse est juste, affiché après la réponse (QCM-04). Texte simple ; une ligne vide sépare deux paragraphes'],
            'reponses' => ['type' => 'array', 'minItems' => QcmDraftChecker::MIN_ANSWERS, 'maxItems' => QcmDraftChecker::MAX_ANSWERS, 'description' => 'De 2 à 6 réponses, une seule correcte (QCM-01)', 'items' => self::schema([
                'texte' => $text,
                'correcte' => ['type' => 'boolean'],
                'explication' => ['type' => ['string', 'null'], 'maxLength' => 1000, 'description' => 'Pour une réponse fausse : pourquoi elle ne convient pas (QCM-04)'],
            ], ['texte', 'correcte'])],
            'themeId' => self::ID + ['description' => 'Thème du QCM (lister_catalogue_qcm)'],
            'niveauId' => self::ID + ['description' => 'Niveau du QCM (lister_catalogue_qcm)'],
        ];

        return [
            [
                new Tool('lister_catalogue_qcm', 'Lister les thèmes et niveaux des QCM', self::schema([]), "Thèmes et niveaux du parcours QCM public, avec leurs identifiants et le nombre de QCM publiés. Un QCM publié apparaît dans la série de son thème et de son niveau.", $readOnly),
                fn () => $this->qcmDrafts->catalogue() + ['adressePublique' => $this->qcmPublicUrl()],
            ],
            [
                new Tool('creer_theme_qcm', 'Créer un thème de QCM', self::schema(['nom' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100]], ['nom']), 'Ajoute un thème au catalogue QCM public. Si le même nom existe déjà, renvoie son identifiant sans créer de doublon. À utiliser à la demande ou après accord de la personne.', $write),
                fn (array $args) => $this->qcmDrafts->createTheme((string) $args['nom']),
            ],
            [
                new Tool('creer_niveau_qcm', 'Créer un niveau de QCM', self::schema(['nom' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255]], ['nom']), 'Ajoute un niveau au catalogue QCM public. Si le même nom existe déjà, renvoie son identifiant sans créer de doublon. À utiliser à la demande ou après accord de la personne.', $write),
                fn (array $args) => $this->qcmDrafts->createLevel((string) $args['nom']),
            ],
            [
                new Tool('lister_brouillons_qcm', 'Lister les brouillons de QCM', self::schema([]), 'Brouillons de QCM (brouillon, a_corriger, publie), avec leur auteur. Leurs identifiants sont distincts de ceux des cours.', $readOnly),
                fn () => $this->qcmDrafts->list(),
            ],
            [
                new Tool('voir_brouillon_qcm', 'Voir un brouillon de QCM', self::schema(['id' => self::ID], ['id']), "Question, réponses, explications, thème, niveau et relecture éventuelle d'un brouillon de QCM.", $readOnly),
                fn (array $args) => $this->qcmDrafts->show((int) $args['id']),
            ],
            [
                new Tool('creer_brouillon_qcm', 'Créer un brouillon de QCM', self::schema($qcmFields, ['question', 'explication', 'reponses', 'themeId', 'niveauId']), "Crée un QCM en brouillon, invisible sur le site. Refusé, sans rien enregistrer, s'il déclenche un contrôle bloquant (une seule bonne réponse, données fictives…). Renvoie les avertissements restants.", $write),
                fn (array $args) => $this->qcmDrafts->create($args, $agent),
            ],
            [
                new Tool('modifier_brouillon_qcm', 'Modifier un brouillon de QCM', self::schema(['id' => self::ID] + $qcmFields, ['id']), "Modifie les champs envoyés d'un brouillon de QCM non publié. Toute modification efface la relecture. Refusé, sans rien changer, si le résultat déclenche un contrôle bloquant.", $write),
                fn (array $args) => $this->qcmDrafts->update((int) $args['id'], array_diff_key($args, ['id' => true])),
            ],
            [
                new Tool('verifier_brouillon_qcm', 'Vérifier un brouillon de QCM', self::schema(['id' => self::ID], ['id']), 'Contrôles automatiques du QCM (regles/controles.md) et état de la relecture, avec le titre des règles concernées. Dit si le QCM est publiable. Ne modifie rien.', $readOnly),
                fn (array $args) => $this->withRuleTitles($this->qcmDrafts->check((int) $args['id'])),
            ],
            [
                new Tool('enregistrer_relecture_qcm', 'Enregistrer la relecture d\'un QCM', self::schema([
                    'id' => self::ID,
                    'avis' => ['type' => 'string', 'enum' => ['favorable', 'reserves', 'defavorable']],
                    'resume' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 2000],
                    'points' => ['type' => 'array', 'maxItems' => 50, 'items' => self::schema([
                        'regle' => ['type' => ['string', 'null'], 'pattern' => self::RULE_ID['pattern'], 'description' => 'Identifiant de la règle concernée, par exemple QCM-02'],
                        'constat' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000],
                        'suggestion' => ['type' => ['string', 'null'], 'maxLength' => 1000],
                    ], ['regle', 'constat'])],
                ], ['id', 'avis', 'resume', 'points']), "Réservé au connecteur relecteur, différent de celui qui a rédigé le QCM. Enregistre l'avis (favorable, reserves, defavorable) et les constats rattachés aux règles. Avec des réserves ou un avis défavorable, le QCM passe « a_corriger ». Ne modifie pas le QCM.", $write),
                fn (array $args) => $this->qcmDrafts->review((int) $args['id'], array_diff_key($args, ['id' => true]), $agent),
            ],
            [
                new Tool('publier_qcm', 'Publier un QCM', self::schema([
                    'id' => self::ID,
                    'apprenantIds' => $learnerIds + ['description' => 'QCM personnalisé : le QCM est publié réservé à ces apprenants (lister_apprenants), jamais visible par les autres'],
                ], ['id']), "Publie le QCM sur le site si aucun contrôle ne bloque et si un autre connecteur l'a relu avec un avis favorable. Crée le QCM et toutes ses réponses ensemble, une seule fois. Avec apprenantIds, c'est un QCM personnalisé, visible seulement par ces apprenants. Sinon, renvoie ce qui bloque.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: true)),
                fn (array $args) => $this->qcmDrafts->publish((int) $args['id'], $this->acces->learners(array_map('intval', (array) ($args['apprenantIds'] ?? [])))) + ['adressePublique' => $this->qcmPublicUrl()],
            ],
        ];
    }

    /**
     * Outils des diaporamas (docs/PLAN_DIAPORAMAS.md) : synthèse structurée, relue par l'autre connecteur, puis publiée.
     *
     * @return list<array{0: Tool, 1: \Closure(array<string, mixed>): mixed}>
     */
    private function diaporamaTools(string $agent, ToolAnnotations $readOnly, ToolAnnotations $write): array
    {
        $court = ['type' => 'string', 'maxLength' => 120];
        $points = ['type' => 'array', 'maxItems' => DiaporamaChecker::MAX_POINTS, 'items' => $court, 'description' => sprintf('%d points au plus, %d mots au plus chacun (DIA-01)', DiaporamaChecker::MAX_POINTS, DiaporamaChecker::MAX_POINT_WORDS)];
        $diapositive = self::schema([
            'modele' => ['type' => 'string', 'enum' => array_keys(DiaporamaChecker::MODELES), 'description' => 'Mise en page de la diapositive (voir modeles dans preparer_diaporama)'],
            'titre' => ['type' => 'string', 'maxLength' => DiaporamaChecker::MAX_TITLE_LENGTH, 'description' => "L'idée de la diapositive, pas seulement son thème (facultatif pour titre et fin)"],
            'sousTitre' => $court + ['description' => 'Modèle titre'],
            'public' => $court + ['description' => 'Modèle titre : public visé, sinon celui du parcours'],
            'duree' => ['type' => ['integer', 'string'], 'description' => 'Modèles titre et exercice : durée en minutes'],
            'points' => $points,
            'colonnes' => ['type' => 'array', 'minItems' => 2, 'maxItems' => 2, 'description' => 'Modèle comparaison : deux colonnes, par exemple « À faire » et « À éviter »', 'items' => self::schema([
                'titre' => $court,
                'points' => $points,
            ], ['titre', 'points'])],
            'capture' => self::ID + ['description' => 'Modèle capture : identifiant de capture (captures de preparer_diaporama ou lister_captures)'],
            'consigne' => ['type' => 'string', 'maxLength' => 300, 'description' => "Modèle exercice : ce qu'il faut faire, avec des données fictives"],
            'question' => ['type' => 'string', 'maxLength' => 300, 'description' => 'Modèle question'],
            'reponses' => ['type' => 'array', 'maxItems' => 4, 'items' => $court, 'description' => 'Modèle question : 2 à 4 réponses ; la bonne réponse et son explication vont dans les notes, jamais à l\'écran'],
            'lien' => ['type' => 'string', 'format' => 'uri', 'description' => 'Modèle fin : adresse du cours complet'],
            'sources' => ['type' => 'array', 'maxItems' => 4, 'description' => 'Modèle fin : pages officielles citées', 'items' => self::schema([
                'libelle' => $court,
                'url' => ['type' => 'string', 'format' => 'uri'],
            ], ['libelle'])],
            'date' => $court + ['description' => 'Modèle fin : date du diaporama, par exemple « 26 octobre 2026 »'],
            'notes' => ['type' => 'string', 'maxLength' => 2000, 'description' => sprintf('Notes de présentation, de %d à %d caractères : ce que la personne qui présente dit (DIA-03). Facultatives sur la première et la dernière diapositive', ...DiaporamaChecker::NOTES_LENGTH)],
        ], ['modele']);
        $champs = [
            'titre' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'sousTitre' => ['type' => 'string', 'maxLength' => 255],
            'diapositives' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 20, 'items' => $diapositive, 'description' => 'De 6 à 12 diapositives pour un cours, de 10 à 20 pour un chapitre ; la première est le modèle titre, la dernière le modèle fin'],
        ];
        $cible = [
            'coursId' => self::ID + ['description' => "Diaporama d'un cours"],
            'moduleId' => self::ID + ['description' => "Ou diaporama d'un chapitre (module de lister_catalogue)"],
        ];

        return [
            [
                new Tool('preparer_diaporama', 'Préparer un diaporama', self::schema($cible), "À lire avant d'écrire : texte du cours (ou de tous les cours du chapitre), public, modèles de diapositive et leurs champs, nombre de diapositives attendu, captures déjà utilisées, cours du chapitre pas encore publiés et diaporama existant. Ne modifie rien.", $readOnly),
                fn (array $args) => $this->diaporamas->source(isset($args['coursId']) ? (int) $args['coursId'] : null, isset($args['moduleId']) ? (int) $args['moduleId'] : null),
            ],
            [
                new Tool('lister_diaporamas', 'Lister les diaporamas', self::schema([]), 'Diaporamas (brouillon, a_corriger, publie), avec leur cible, leur auteur et leur nombre de diapositives.', $readOnly),
                fn () => $this->diaporamas->list(),
            ],
            [
                new Tool('voir_diaporama', 'Voir un diaporama', self::schema(['id' => self::ID], ['id']), 'Titre, diapositives, notes et relecture éventuelle d\'un diaporama.', $readOnly),
                fn (array $args) => $this->diaporamas->show((int) $args['id']),
            ],
            [
                new Tool('creer_diaporama', 'Créer un diaporama', self::schema($cible + $champs, ['titre', 'diapositives']), "Crée le diaporama d'un cours (coursId) ou d'un chapitre (moduleId), en brouillon, invisible sur le site. Une cible n'a qu'un diaporama. Refusé, sans rien enregistrer, s'il déclenche un contrôle bloquant : copie du cours, trop de points, notes manquantes, données réelles… Renvoie les avertissements restants.", $write),
                fn (array $args) => $this->diaporamas->create($args, $agent),
            ],
            [
                new Tool('modifier_diaporama', 'Modifier un diaporama', self::schema(['id' => self::ID] + $champs, ['id']), "Remplace les champs envoyés (diapositives : la liste complète). Toute modification efface la relecture ; celle d'un diaporama publié est visible tout de suite. Refusé, sans rien changer, si le résultat déclenche un contrôle bloquant.", $write),
                fn (array $args) => $this->diaporamas->update((int) $args['id'], array_diff_key($args, ['id' => true])),
            ],
            [
                new Tool('verifier_diaporama', 'Vérifier un diaporama', self::schema(['id' => self::ID], ['id']), 'Contrôles automatiques du diaporama (regles/controles.md) et état de la relecture, avec le titre des règles concernées. Dit si le diaporama est publiable. Ne modifie rien.', $readOnly),
                fn (array $args) => $this->withRuleTitles($this->diaporamas->check((int) $args['id'])),
            ],
            [
                new Tool('enregistrer_relecture_diaporama', 'Enregistrer la relecture d\'un diaporama', self::schema([
                    'id' => self::ID,
                    'avis' => ['type' => 'string', 'enum' => ['favorable', 'reserves', 'defavorable']],
                    'resume' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 2000],
                    'points' => ['type' => 'array', 'maxItems' => 50, 'items' => self::schema([
                        'regle' => ['type' => ['string', 'null'], 'pattern' => self::RULE_ID['pattern'], 'description' => 'Identifiant de la règle concernée, par exemple DIA-02'],
                        'constat' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000],
                        'suggestion' => ['type' => ['string', 'null'], 'maxLength' => 1000],
                    ], ['regle', 'constat'])],
                ], ['id', 'avis', 'resume', 'points']), "Réservé au connecteur relecteur, différent de celui qui a rédigé le diaporama. Enregistre l'avis (favorable, reserves, defavorable) et les constats rattachés aux règles. Avec des réserves ou un avis défavorable, le brouillon passe « a_corriger ». Ne modifie pas le diaporama.", $write),
                fn (array $args) => $this->diaporamas->review((int) $args['id'], array_diff_key($args, ['id' => true]), $agent),
            ],
            [
                new Tool('publier_diaporama', 'Publier un diaporama', self::schema(['id' => self::ID], ['id']), "Publie le diaporama si aucun contrôle ne bloque et si un autre connecteur l'a relu avec un avis favorable : les boutons .pptx, PDF et aperçu apparaissent sur la page du cours ou du parcours. Sinon, renvoie ce qui bloque.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: true)),
                fn (array $args) => $this->diaporamas->publish((int) $args['id']) + ['adressePublique' => $this->diaporamaPublicUrl((int) $args['id'])],
            ],
        ];
    }

    private function diaporamaPublicUrl(int $diaporamaId): ?string
    {
        $path = $this->diaporamas->publicPath($diaporamaId);

        return $path === null ? null : $this->publicUrl->base($this->requestStack->getCurrentRequest() ?? Request::createFromGlobals()).$path;
    }

    /**
     * Crée le contenu puis le réserve aux apprenants demandés ; les identifiants sont vérifiés avant de créer quoi que ce soit.
     *
     * @param array<string, mixed> $args
     * @param \Closure(array<string, mixed>): array<string, mixed> $create
     * @return array<string, mixed>
     */
    private function createReserved(string $type, array $args, \Closure $create): array
    {
        $learnerIds = array_map('intval', (array) ($args['apprenantIds'] ?? []));
        $this->acces->learners($learnerIds);
        $created = $create(array_diff_key($args, ['apprenantIds' => true]));
        if ($learnerIds === []) {
            return $created;
        }

        return $created + ['acces' => $this->acces->grant($type, (int) $created['id'], $learnerIds)];
    }

    private function qcmPublicUrl(): string
    {
        return $this->publicUrl->base($this->requestStack->getCurrentRequest() ?? Request::createFromGlobals()).'/spa/qcm';
    }

    /**
     * @param \Closure(array<string, mixed>): mixed $callback
     * @param array<string, mixed> $args
     */
    private function call(\Closure $callback, array $args): CallToolResult
    {
        try {
            $result = $callback($args);
        } catch (RedactionError $error) {
            $text = 'Le site a refusé : '.$error->getMessage();
            if ($error->details !== null) {
                $text .= "\n".json_encode($error->details, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
            }

            throw new ToolCallException($text, previous: $error);
        }

        $text = \is_string($result) ? $result : (string) json_encode($result, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);

        return new CallToolResult([new TextContent($text)]);
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>|string
     */
    private function readRules(array $args): array|string
    {
        $files = $this->regles->files();
        $rules = $this->regles->rules();

        if (isset($args['fichier'])) {
            $file = (string) $args['fichier'];
            if (!isset($files[$file])) {
                throw new RedactionError('Fichier inconnu. Fichiers : '.implode(', ', array_keys($files)), RedactionError::NOT_FOUND);
            }

            return $files[$file];
        }

        $ids = array_map('strval', (array) ($args['identifiants'] ?? []));
        if ($ids !== []) {
            $unknown = array_diff($ids, array_keys($rules));
            if ($unknown !== []) {
                throw new RedactionError('Règles inconnues : '.implode(', ', $unknown), RedactionError::NOT_FOUND);
            }

            return implode("\n\n", array_map(static fn (string $id) => $rules[$id]['texte'], $ids))."\n\n---\n".($files['sources.md'] ?? '');
        }

        return [
            'statut' => $this->regles->statut(),
            'regles' => array_values(array_map(static fn (array $rule) => ['id' => $rule['id'], 'titre' => $rule['titre'], 'fichier' => $rule['fichier']], $rules)),
            'fichiers' => array_keys($files),
            'modeles' => array_keys($this->regles->modeles()),
        ];
    }

    /**
     * @param array{controles: list<array<string, mixed>>} $report
     * @return array<string, mixed>
     */
    private function withRuleTitles(array $report): array
    {
        $report['controles'] = array_map(fn (array $issue) => $issue + ['regleTitre' => $this->regles->ruleTitle(isset($issue['regle']) ? (string) $issue['regle'] : null)], $report['controles']);

        return $report;
    }

    /**
     * @param array<string, mixed> $properties
     * @param list<string> $required
     * @return array<string, mixed>
     */
    private static function schema(array $properties, array $required = []): array
    {
        // Le SDK transforme une liste de propriétés vide en objet JSON {}
        $schema = ['type' => 'object', 'properties' => $properties, 'additionalProperties' => false];
        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    public static function writingPrompt(string $sujet, string $niveau): string
    {
        $pour = $niveau === '' ? '' : " (niveau « {$niveau} »)";

        return <<<TXT
            Prépare un brouillon de cours sur : « {$sujet} »{$pour}.

            Démarche :
            1. lire_regles sans argument, puis lis en entier les règles RED, COD, SRC et DON.
            2. lister_catalogue : choisis le parcours (Symfony, Vue…) puis le module du cours. Pour une série de cours, propose d'abord des modules par thème et crée-les avec l'accord de la personne (creer_module). S'il manque une rubrique, un parcours ou un niveau, procède de même (creer_rubrique, creer_parcours avec rubriqueId, creer_niveau).
            3. obtenir_modele « cours » et suis sa structure.
            4. Rédige en langage clair (RED-01) : une idée par phrase, chaque terme technique défini à sa première apparition.
            5. Code (COD-01 à COD-03) : chaque bloc en <pre><code class="language-…">, des exemples complets qui respectent la version annoncée, la pratique recommandée par la documentation officielle.
            6. Sources (SRC-01) : vérifie le contenu dans la documentation officielle, renseigne sourceOfficielle et verifieLe, et mets un lien vers cette documentation dans le cours.
            7. Données (DON-01) : aucune vraie clé, aucun jeton ni mot de passe, courriels en @example.com.
            8. Captures, seulement si une image aide (CAP-01 à CAP-09) : pages publiques en https, sans connexion. Réutilise les captures existantes (lister_captures) ; sinon fais-les avec capturer_page (repères numérotés repris dans le texte). Pour une image fournie par la personne, utilise preparer_depot_image. Place le marqueur [capture:ID] sur une ligne seule, jamais de balise <img>. N'invente jamais de capture.
            9. creer_brouillon avec moduleId (un cours sans module tombe dans un chapitre générique « Cours »), puis verifier_brouillon et corrige jusqu'à ne plus avoir de contrôle bloquant.
            10. Termine par un compte rendu : identifiant du brouillon, règles appliquées, avertissements restants, captures à prévoir. Demande ensuite une relecture avec l'AUTRE connecteur (par exemple ChatGPT si tu es Claude) et la consigne « relire_brouillon ».
            11. Après un avis favorable : publier_cours (sansModule seulement si la personne l'a choisi). Pour ranger ou déplacer des cours déjà créés : classer_cours ; pour changer une image d'un cours publié : remplacer_capture. Avec des réserves : corrige, puis redemande une relecture. Donne enfin l'adresse du cours publié.

            Ne publie jamais sans relecture favorable d'un autre connecteur.
            TXT;
    }

    public static function reviewPrompt(int $id): string
    {
        return <<<TXT
            Tu es l'agent relecteur. Relis le brouillon {$id} sans le modifier.

            1. voir_brouillon {$id}, puis verifier_brouillon {$id} pour les contrôles automatiques.
            2. lire_regles sans argument, puis lis le texte complet des règles concernées.
            3. Grille de relecture (cite l'identifiant de règle pour chaque constat) :
               - Justesse technique : chaque affirmation et chaque exemple sont conformes à la documentation officielle de la version annoncée (SRC-01, COD-02). Vérifie-les toi-même ; ne te fie pas à l'auteur.
               - COD-01 à COD-03 : langage indiqué pour chaque bloc, exemples complets ou omissions signalées, bonne pratique montrée, aucune API dépréciée présentée sans avertissement.
               - RED-01 et RED-03 : langage clair, termes définis, structure objectif → notion → exemple → mise en pratique.
               - DON-01 : aucun secret ni donnée réelle, même dans le code.
               - CAP-01 à CAP-09 : captures de pages publiques, sources et dates, repères numérotés, aucune donnée de compte visible.
            4. Avis :
               - « defavorable » si le contenu ou un exemple est faux, dangereux (faille de sécurité, secret exposé) ou ne correspond pas à la version annoncée ;
               - « reserves » s'il reste des corrections de rédaction ou d'exemples ;
               - « favorable » sinon.
            5. enregistrer_relecture avec l'avis, un résumé et un constat par problème (règle, constat, suggestion).

            Ne modifie jamais le brouillon, ne le soumets pas et ne le publie pas : l'auteur corrige ou publie. Un avis « favorable » permet la publication immédiate sur le site : ne le donne que si le cours est prêt.
            TXT;
    }

    public static function qcmWritingPrompt(string $sujet, string $niveau): string
    {
        $pour = $niveau === '' ? '' : " (niveau « {$niveau} »)";

        return <<<TXT
            Prépare un QCM sur : « {$sujet} »{$pour}.

            Démarche :
            1. lire_regles avec les identifiants QCM-01 à QCM-04, RED-01 et DON-01, et lis-les en entier.
            2. lister_catalogue_qcm : choisis le thème et le niveau adaptés. Si le thème ou le niveau manque, propose un nom pertinent à la personne et demande son accord ; après accord, utilise creer_theme_qcm et/ou creer_niveau_qcm, puis relis lister_catalogue_qcm pour récupérer les identifiants. Un nom déjà existant renvoie son identifiant sans créer de doublon.
            3. Écris une seule question claire (QCM-03), vérifiée dans la documentation officielle.
            4. Propose de 2 à 6 réponses, dont UNE seule correcte (QCM-01). Les réponses fausses sont des erreurs réelles (méthode inexistante, option d'une autre version), sans piège ni « toutes les réponses » (QCM-02).
            5. Écris l'explication générale : pourquoi la bonne réponse est juste. Ajoute pour chaque réponse fausse pourquoi elle ne convient pas (QCM-04).
            6. Données : aucun secret ni donnée réelle (DON-01).
            7. creer_brouillon_qcm, puis verifier_brouillon_qcm. Un contrôle bloquant refuse l'enregistrement : corrige et recommence. Traite les avertissements.
            8. Termine par un compte rendu : identifiant du brouillon de QCM, thème, niveau, avertissements restants. Demande ensuite une relecture avec l'AUTRE connecteur (par exemple ChatGPT si tu es Claude) et la consigne « relire_qcm ».
            9. Après un avis favorable : publier_qcm, puis donne l'adresse publique, le thème et le niveau à choisir pour retrouver le QCM. Avec des réserves : modifier_brouillon_qcm, puis redemande une relecture.

            Ne publie jamais sans relecture favorable d'un autre connecteur.
            TXT;
    }

    public static function diaporamaWritingPrompt(string $cible, int $id): string
    {
        $argument = $cible === 'module' ? "moduleId {$id}" : "coursId {$id}";
        $nombre = $cible === 'module' ? '10 à 20' : '6 à 12';

        return <<<TXT
            Prépare le diaporama du {$cible} {$id} ({$argument}).

            Démarche :
            1. lire_regles avec les identifiants DIA-01 à DIA-06, RED-01, RED-03 et DON-01, et lis-les en entier.
            2. preparer_diaporama avec {$argument} : lis tout le texte source, les modèles et leurs champs. Pour un chapitre, si des cours ne sont pas publiés, arrête-toi et dis lesquels.
            3. Choisis ce qui compte pour une présentation : objectifs, notions clés, étapes, erreurs fréquentes, une activité. {$nombre} diapositives : la première au modèle titre, la dernière au modèle fin (lien vers le cours complet, sources, date).
            4. Une idée par diapositive (DIA-01) : un titre qui dit l'idée, 5 points au plus, 12 mots au plus par point. Des groupes de mots suffisent.
            5. Synthétise, ne copie pas (DIA-02) : aucun passage de plus de 8 mots repris du cours sur une diapositive. Pour un chapitre, fais une vraie synthèse (fil conducteur, progression), pas la suite des cours.
            6. Le détail va dans les notes de présentation (DIA-03), de 300 à 900 caractères, en langage clair : ce que dit la personne qui présente, une question à poser au groupe. Pour une question, la bonne réponse et son explication sont dans les notes.
            7. Faire pratiquer (DIA-04) : au moins une diapositive exercice ou question pour un cours, avec des données fictives (DON-01).
            8. Captures (DIA-05) : seulement celles du cours ou de lister_captures, par leur identifiant. N'invente jamais de capture.
            9. N'écris aucun style, couleur ni police : le site applique son thème.
            10. creer_diaporama, puis verifier_diaporama. Un contrôle bloquant refuse l'enregistrement : corrige et recommence. Traite les avertissements.
            11. Termine par un compte rendu : identifiant du diaporama, nombre de diapositives, avertissements restants. Demande ensuite une relecture avec l'AUTRE connecteur (par exemple ChatGPT si tu es Claude) et la consigne « relire_diaporama ».
            12. Après un avis favorable : publier_diaporama et donne l'adresse publique. Avec des réserves : modifier_diaporama, puis redemande une relecture.

            Ne publie jamais sans relecture favorable d'un autre connecteur.
            TXT;
    }

    public static function diaporamaReviewPrompt(int $id): string
    {
        return <<<TXT
            Tu es l'agent relecteur. Relis le diaporama {$id} sans le modifier.

            1. voir_diaporama {$id}, puis verifier_diaporama {$id} pour les contrôles automatiques.
            2. preparer_diaporama avec la cible du diaporama (coursId ou moduleId) pour lire le texte source.
            3. lire_regles avec les identifiants DIA-01 à DIA-06, RED-01, RED-03 et DON-01.
            4. Grille de relecture (cite l'identifiant de règle pour chaque constat) :
               - fidélité : rien de faux ni d'ajouté par rapport au cours ; les limites dites dans le cours restent présentes ;
               - DIA-01 et DIA-02 : une idée par diapositive, des titres qui disent l'idée, une vraie synthèse et pas une copie ;
               - DIA-03 : des notes utiles, assez précises pour présenter sans avoir rédigé le diaporama ; la réponse d'une question est dans les notes ;
               - DIA-04 et RED-03 : une activité réaliste, avec des données fictives ;
               - DIA-05 et DIA-06 : captures pertinentes, dernière diapositive avec le lien du cours, les sources et la date, aucune marque d'éditeur ni donnée personnelle.
            5. Avis :
               - « defavorable » si une information est fausse ou si une donnée n'est pas fictive ;
               - « reserves » s'il reste des corrections de synthèse, de notes ou de rédaction ;
               - « favorable » sinon.
            6. enregistrer_relecture_diaporama avec l'avis, un résumé et un constat par problème (règle, constat, suggestion).

            Ne modifie jamais le diaporama et ne le publie pas : l'auteur corrige ou publie. Un avis « favorable » permet la publication immédiate : ne le donne que si le diaporama est prêt à être projeté.
            TXT;
    }

    public static function qcmReviewPrompt(int $id): string
    {
        return <<<TXT
            Tu es l'agent relecteur. Relis le brouillon de QCM {$id} sans le modifier.

            1. voir_brouillon_qcm {$id}, puis verifier_brouillon_qcm {$id} pour les contrôles automatiques.
            2. lire_regles avec les identifiants QCM-01 à QCM-04, RED-01 et DON-01.
            3. Grille de relecture (cite l'identifiant de règle pour chaque constat) :
               - QCM-01 : la bonne réponse est juste sans discussion, d'après la documentation officielle ; aucune réponse fausse n'est défendable.
               - QCM-02 : les réponses fausses sont plausibles, sans piège, de forme proche de la bonne.
               - QCM-03 et RED-01 : la question est courte, claire et utile.
               - QCM-04 : l'explication dit pourquoi ; chaque réponse fausse a son explication.
               - DON-01 : aucun secret ni donnée réelle.
               - Le thème et le niveau conviennent à la question.
            4. Avis :
               - « defavorable » si la bonne réponse est fausse ou discutable ;
               - « reserves » s'il reste des corrections de rédaction, de réponses fausses ou d'explication ;
               - « favorable » sinon.
            5. enregistrer_relecture_qcm avec l'avis, un résumé et un constat par problème (règle, constat, suggestion).

            Ne modifie jamais le QCM et ne le publie pas : l'auteur corrige ou publie. Un avis « favorable » permet la publication immédiate sur le site : ne le donne que si le QCM est prêt.
            TXT;
    }
}
