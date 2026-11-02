<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Entity\Category;
use App\Service\OAuth\PublicUrl;
use App\Service\Redaction\AccesPersonnaliseService;
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
    public const NAME = 'mediation-numerique-redaction';
    public const VERSION = '0.9.0';

    public const INSTRUCTIONS = <<<'TXT'
        Assistant de rédaction du site de formation à la médiation numérique.
        Vous préparez des cours en brouillon, puis vous les publiez avec publier_cours.
        Pour modifier un cours déjà publié, lisez lister_cours_publies puis voir_brouillon avec son identifiant,
        et utilisez modifier_brouillon : les changements sont immédiatement visibles sur le site.
        Pour retirer du site un cours, un module ou un parcours à la demande de la personne : mettre_en_brouillon.
        Avant d'écrire, lisez les règles (lire_regles) et le gabarit du public (obtenir_modele).
        Un brouillon doit être relu par un AUTRE connecteur (par exemple ChatGPT pour un brouillon rédigé
        avec Claude) avec la consigne « relire_brouillon » : la publication demande aucun contrôle bloquant et un
        avis favorable. Toute modification efface la relecture ; celle d'un cours publié est visible tout de suite.
        Pour un QCM : consigne « rediger_qcm », outils *_qcm, relecture par l'autre connecteur avec « relire_qcm »,
        puis publier_qcm. Si le catalogue n'a pas de thème ou de niveau adapté, créez-le avec creer_theme_qcm
        ou creer_niveau_qcm (après accord de la personne), puis relisez le catalogue avant de créer le QCM.
        Un QCM publié rejoint la série publique de son thème et de son niveau.
        Cours et exercices personnalisés : un cours, un parcours ou un QCM peut être réservé à certains apprenants
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
            ->setServerInfo(self::NAME, self::VERSION, 'Préparer des brouillons de cours et de QCM conformes aux règles de la médiation numérique.')
            ->setInstructions(self::INSTRUCTIONS)
            ->setSession(new FileSessionStore($this->sessionDir))
            ->setLogger($this->logger);

        foreach ($this->tools($agent) as [$tool, $callback]) {
            $builder->add($tool, new CallbackTool(fn (array $args) => $this->call($callback, $args)));
        }

        $builder->add(
            new Prompt('rediger_cours', 'Rédiger un cours', 'Démarche complète pour préparer un brouillon conforme aux règles de la médiation numérique.', [
                new PromptArgument('sujet', 'Sujet du cours, par exemple « trouver son espace personnel sur le site de la CAF »', true),
                new PromptArgument('public', 'apprendre (adultes et seniors) ou accompagner (médiateurs)', true),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::writingPrompt((string) ($args['sujet'] ?? ''), ($args['public'] ?? '') === 'accompagner' ? 'accompagner' : 'apprendre')]),
        );
        $builder->add(
            new Prompt('relire_brouillon', 'Relire un brouillon', "Consigne de l'agent relecteur : contrôle selon la grille du médiateur, sans modifier le contenu.", [
                new PromptArgument('id', 'Identifiant du brouillon', true),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::reviewPrompt((int) ($args['id'] ?? 0))]),
        );

        $builder->add(
            new Prompt('rediger_qcm', 'Rédiger un QCM', "Démarche pour préparer un QCM (question, réponses, correction) et le faire relire avant publication.", [
                new PromptArgument('sujet', 'Geste ou notion à vérifier, par exemple « reconnaître un faux message de livraison »', true),
                new PromptArgument('public', 'apprendre (adultes et seniors) ou accompagner (médiateurs)', false),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::qcmWritingPrompt((string) ($args['sujet'] ?? ''), ($args['public'] ?? '') === 'accompagner' ? 'accompagner' : 'apprendre')]),
        );
        $builder->add(
            new Prompt('relire_qcm', 'Relire un QCM', "Consigne de l'agent relecteur d'un QCM : justesse, réponses fausses, correction et clarté, sans modifier le QCM.", [
                new PromptArgument('id', 'Identifiant du brouillon de QCM', true),
            ]),
            new CallbackPrompt(fn (array $args) => ['user' => self::qcmReviewPrompt((int) ($args['id'] ?? 0))]),
        );

        foreach ($this->regles->files() as $file => $text) {
            $builder->add(
                new ResourceDefinition('regles:///'.$file, 'regles-'.basename($file, '.md'), 'Règles — '.$file, 'Référentiel de la médiation numérique (regles/)', 'text/markdown'),
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
            'contenu' => ['type' => 'string', 'maxLength' => 200000, 'description' => 'HTML du cours, selon le gabarit ; captures par marqueur [capture:ID] sur une ligne seule'],
            'sourceOfficielle' => ['type' => ['string', 'null'], 'format' => 'uri', 'description' => 'Page officielle de référence (fiche de service)'],
            'verifieLe' => ['type' => ['string', 'null'], 'pattern' => self::DATE['pattern'], 'description' => 'Date de vérification auprès de la source, AAAA-MM-JJ'],
        ];

        return [
            [
                new Tool('lister_catalogue', 'Lister le catalogue', self::schema([]), 'Parcours (avec leur public : apprendre ou accompagner), modules, niveaux et sites capturables, avec leurs identifiants.', $readOnly),
                fn () => $this->redaction->catalogue(),
            ],
            [
                new Tool('creer_parcours', 'Créer un parcours', self::schema([
                    'nom' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255, 'description' => "Par exemple « J'apprends le numérique »"],
                    'public' => ['type' => 'string', 'enum' => Category::AUDIENCES, 'description' => 'apprendre (adultes et seniors) ou accompagner (médiateurs)'],
                    'description' => ['type' => ['string', 'null'], 'maxLength' => 2000],
                    'niveauId' => ['type' => ['integer', 'null'], 'minimum' => 1],
                ], ['nom', 'public']), "Ajoute un parcours au catalogue. Il n'apparaît sur le site qu'une fois un de ses cours publié. À créer seulement à la demande de la personne.", $write),
                fn (array $args) => $this->redaction->createParcours($args),
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
                new Tool('lire_regles', 'Lire les règles de la médiation numérique', self::schema([
                    'identifiants' => ['type' => 'array', 'items' => self::RULE_ID, 'maxItems' => 40, 'description' => 'Identifiants de règles, par exemple ["POS-01", "DON-04"]'],
                    'fichier' => ['type' => 'string', 'description' => "Nom d'un fichier de regles/, par exemple controles.md"],
                ]), "Sans argument : statut du référentiel et liste des règles. Avec des identifiants (POS-01, LIM-01…) : texte complet de ces règles et leurs sources. Avec 'fichier' : un fichier entier (README.md, sources.md, controles.md…).", $readOnly),
                fn (array $args) => $this->readRules($args),
            ],
            [
                new Tool('obtenir_modele', 'Obtenir un gabarit de cours', self::schema([
                    'modele' => ['type' => 'string', 'enum' => ['cours-apprendre', 'cours-accompagner', 'fiche-service']],
                ], ['modele']), 'Gabarit HTML à suivre : cours-apprendre (adultes et seniors), cours-accompagner (médiateurs) ou fiche-service (CAF, France Travail…).', $readOnly),
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
                    'slug' => ['type' => 'string', 'pattern' => '^[a-z0-9]+(-[a-z0-9]+)*$', 'maxLength' => 90, 'description' => 'Adresse du cours, par exemple caf-trouver-mon-compte'],
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
                new Tool('supprimer_module', 'Supprimer un module vide', self::schema([
                    'moduleId' => self::ID + ['description' => 'Module à supprimer (lister_catalogue)'],
                ], ['moduleId']), "Supprime définitivement un module (chapitre) qui ne contient plus aucun cours, quel que soit leur statut. Refusé, sans rien supprimer, si le module n'est pas vide : la réponse liste les cours restants, à déplacer d'abord avec classer_cours. Jamais de suppression en cascade : aucun cours n'est touché. À faire seulement à la demande de la personne.", new ToolAnnotations(readOnlyHint: false, destructiveHint: true, idempotentHint: false, openWorldHint: true)),
                fn (array $args) => $this->redaction->deleteModule((int) $args['moduleId']),
            ],
            [
                new Tool('classer_cours', 'Classer des cours dans un module', self::schema([
                    'coursIds' => ['type' => 'array', 'items' => self::ID, 'minItems' => 1, 'maxItems' => 100, 'description' => "Cours à ranger, quel que soit leur statut (publiés compris), dans l'ordre où ils doivent apparaître dans le module : le premier identifiant devient le rang 1"],
                    'moduleId' => ['type' => ['integer', 'null'], 'minimum' => 1, 'description' => 'Module de destination, du même parcours que les cours (lister_catalogue) ; null pour retirer les cours de leur module'],
                ], ['coursIds', 'moduleId']), "Classement éditorial : range plusieurs cours dans un module ou les déplace d'un module à l'autre, et fixe leur ordre d'affichage d'après l'ordre des identifiants (rang 1, 2, 3…), y compris pour ceux déjà dans le module. Ne modifie ni le contenu, ni le statut, ni la relecture. Renvoie les cours classés (ancien et nouveau module, rang), ceux déjà dans ce module, le rang de chacun (ordres) et les refus avec leur raison.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->classifyCourses(array_map('intval', (array) $args['coursIds']), isset($args['moduleId']) ? (int) $args['moduleId'] : null),
            ],
            [
                new Tool('classer_niveau', 'Classer des cours par niveau', self::schema([
                    'coursIds' => ['type' => 'array', 'items' => self::ID, 'minItems' => 1, 'maxItems' => 100, 'description' => 'Cours à classer, quel que soit leur statut (publiés compris)'],
                    'niveauId' => ['type' => ['integer', 'null'], 'minimum' => 1, 'description' => 'Niveau à donner (lister_catalogue), choisi selon la règle NIV-01 (regles/07-niveaux.md) ; null pour retirer le niveau'],
                ], ['coursIds', 'niveauId']), "Classement par niveau : donne le même niveau à plusieurs cours. Ne modifie ni le contenu, ni le statut, ni la relecture. Renvoie les cours classés (ancien et nouveau niveau), ceux déjà à ce niveau et les refus avec leur raison.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true)),
                fn (array $args) => $this->redaction->classifyLevel(array_map('intval', (array) $args['coursIds']), isset($args['niveauId']) ? (int) $args['niveauId'] : null),
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
                    'url' => ['type' => 'string', 'format' => 'uri', 'description' => 'Page publique officielle, par exemple https://www.caf.fr/'],
                    'format' => ['type' => 'string', 'enum' => ['ordinateur', 'mobile'], 'default' => 'ordinateur'],
                    'elements' => ['type' => 'array', 'maxItems' => 9, 'description' => "Éléments à repérer, dans l'ordre des étapes", 'items' => self::schema([
                        'repere' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 9, 'description' => 'Numéro du repère, repris dans le texte du cours'],
                        'texte' => ['type' => 'string', 'maxLength' => 120, 'description' => "Texte visible de l'élément, par exemple « Mon Compte »"],
                        'selecteur' => ['type' => 'string', 'maxLength' => 200, 'description' => 'Ou un sélecteur CSS'],
                    ], ['repere'])],
                    'service' => ['type' => 'string', 'maxLength' => 100],
                    'legende' => ['type' => 'string', 'minLength' => 1, 'description' => 'Ce que la personne doit repérer, en citant les numéros des repères'],
                    'texteAlternatif' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500, 'description' => "Description de l'image et de la position des repères (ACC-01)"],
                    'aRevoirLe' => self::DATE + ['description' => 'Par défaut dans 3 mois'],
                ], ['url', 'legende', 'texteAlternatif']), "Le site ouvre toute page publique en https et en fait la capture, avec des repères numérotés sur les éléments à montrer (CAP-07). Aucun domaine prédéfini : toute adresse publique est possible. Les adresses locales ou privées sont refusées pour protéger le serveur. Jamais d'écran de connexion (refusé : utilisez alors une illustration fictive, CAP-03) ; aucune requête autre que GET et les cookies sont refusés. Une protection anti-robots n'est jamais contournée (CAP-08) : la capture se fait alors à la main dans le back-office. Renvoie le marqueur [capture:ID].", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: true)),
                fn (array $args) => $this->redaction->captureAutomatically($args + ['format' => 'ordinateur', 'elements' => []], $agent),
            ],
            [
                new Tool('preparer_depot_image', "Préparer le dépôt d'une image", self::schema([
                    'type' => ['type' => 'string', 'enum' => ['capture', 'illustration'], 'description' => "capture : page publique d'un site officiel (pageSource et dateCapture obligatoires) ; illustration : image fictive ou pédagogique (schéma, photo d'un objet)"],
                    'legende' => ['type' => 'string', 'minLength' => 1, 'description' => 'Ce que la personne doit repérer, en citant les repères éventuels'],
                    'texteAlternatif' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500, 'description' => "Description de l'image pour qui ne la voit pas (ACC-01)"],
                    'service' => ['type' => ['string', 'null'], 'maxLength' => 100],
                    'pageSource' => ['type' => ['string', 'null'], 'format' => 'uri', 'description' => 'Page officielle capturée (type capture)'],
                    'dateCapture' => ['type' => ['string', 'null'], 'pattern' => self::DATE['pattern'], 'description' => 'Date de la capture, AAAA-MM-JJ (type capture)'],
                    'aRevoirLe' => self::DATE + ['description' => 'Par défaut dans 3 mois'],
                ], ['type', 'legende', 'texteAlternatif']), "Pour une image présente sur l'ordinateur de la personne. Vérifie la fiche de l'image et renvoie une adresse de dépôt à usage unique, valable 10 minutes, et la commande curl pour y envoyer le fichier. Seul un agent qui a accès aux fichiers de l'ordinateur (Claude Code, par exemple) peut faire l'envoi ; la réponse de l'envoi donne le marqueur [capture:ID] (pour la miniature d'un parcours, type illustration puis definir_illustration_parcours). Mêmes règles que les captures (CAP-01, CAP-03 : jamais de données réelles d'un usager).", $write),
                fn (array $args) => $this->redaction->prepareImageDeposit($args, $agent, $this->publicUrl->base($this->requestStack->getCurrentRequest() ?? Request::createFromGlobals())),
            ],
            ...$this->qcmTools($agent, $readOnly, $write, $learnerIds),
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
                    'apprenantIds' => $learnerIds + ['description' => 'Exercice personnalisé : le QCM est publié réservé à ces apprenants (lister_apprenants), jamais visible par les autres'],
                ], ['id']), "Publie le QCM sur le site si aucun contrôle ne bloque et si un autre connecteur l'a relu avec un avis favorable. Crée le QCM et toutes ses réponses ensemble, une seule fois. Avec apprenantIds, c'est un exercice personnalisé, visible seulement par ces apprenants. Sinon, renvoie ce qui bloque.", new ToolAnnotations(readOnlyHint: false, destructiveHint: false, openWorldHint: true)),
                fn (array $args) => $this->qcmDrafts->publish((int) $args['id'], $this->acces->learners(array_map('intval', (array) ($args['apprenantIds'] ?? [])))) + ['adressePublique' => $this->qcmPublicUrl()],
            ],
        ];
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

    public static function writingPrompt(string $sujet, string $audience): string
    {
        $modele = $audience === 'apprendre' ? 'cours-apprendre (ou fiche-service pour un service en ligne)' : 'cours-accompagner';

        return <<<TXT
            Prépare un brouillon de cours sur : « {$sujet} », pour le public « {$audience} ».

            Démarche :
            1. lire_regles sans argument, puis lis en entier les règles POS, LIM, DON, RED, ACC et CAP utiles au sujet.
            2. lister_catalogue : choisis le parcours dont le public est « {$audience} », puis le module (chapitre) du cours. Pour une série de cours, propose d'abord des modules par thème et crée-les avec l'accord de la personne (creer_module). S'il manque un parcours ou un niveau, procède de même (creer_parcours, creer_niveau).
            3. obtenir_modele « {$modele} » et suis sa structure.
            4. Rédige en langage clair (RED-01) : une action par étape, des phrases de moins de 20 mots, aucun jargon pour le public « apprendre ».
            5. Données : uniquement fictives (DON-04) — « Mme Exemple », adresses en @example.com, numéros écrits avec des X.
            6. Captures : pages publiques en https, sans connexion (CAP-01). Tu peux utiliser n'importe quel domaine public ; il n'y a pas de liste à consulter. Réutilise les captures existantes (lister_captures) ; sinon fais-les avec capturer_page (repères numérotés repris dans le texte : « Repère 1 : le bouton… »). Pour une image fournie par la personne sur son ordinateur, utilise preparer_depot_image puis envoie le fichier si tu as accès à ses fichiers. Si la capture automatique est refusée ou indisponible, liste la capture à faire à la main dans le back-office (page, repères, légende proposée). Place le marqueur [capture:ID] sur une ligne seule. Pour un écran de compte personnel, prévois une illustration fictive (CAP-03). Décris toujours l'étape en texte (RED-05). N'invente jamais de capture.
            7. creer_brouillon avec moduleId (un cours sans module tombe dans un chapitre générique « Cours »), niveauId choisi selon la règle NIV-01 (lire_regles fichier « 07-niveaux.md ») (pour le public « apprendre » ; classer_niveau pour un cours existant), et sourceOfficielle avec un lien vers le site officiel pour une fiche de service, puis verifier_brouillon et corrige jusqu'à ne plus avoir de contrôle bloquant.
            8. Termine par un compte rendu : identifiant du brouillon, règles appliquées, avertissements restants, captures à prévoir. Demande ensuite une relecture avec l'AUTRE connecteur (par exemple ChatGPT si tu es Claude) et la consigne « relire_brouillon ».
            9. Après un avis favorable : publier_cours (sansModule seulement si la personne l'a choisi). Pour ranger ou déplacer des cours déjà créés : classer_cours ; pour changer une image d'un cours publié : remplacer_capture. Avec des réserves : corrige, puis redemande une relecture. Donne enfin l'adresse du cours publié.

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
               - POS-01 à POS-06 : la personne fait elle-même ; le cours part de son besoin ; chaque étape est expliquée ; il se termine par une vérification de l'autonomie.
               - LIM-01 à LIM-06 : rien n'invite à faire une démarche à la place de quelqu'un sans mandat ; l'orientation est indiquée ; le site n'est pas présenté comme officiel.
               - DON-01 à DON-05 : aucun identifiant demandé ni conservé ; uniquement des données fictives.
               - RED-01 à RED-05 et ACC-01 à ACC-04 : langage clair, structure montrer / faire faire / vérifier, textes alternatifs pertinents, rien d'important seulement dans une image ou par la couleur.
               - CAP-01 à CAP-09 : captures de pages publiques, sources et dates, repères numérotés, illustrations fictives pour les écrans personnels.
            4. Avis :
               - « defavorable » si une règle POS-01, LIM ou DON n'est pas respectée, ou si le contenu est faux ;
               - « reserves » s'il reste des corrections de rédaction ou d'accessibilité ;
               - « favorable » sinon.
            5. enregistrer_relecture avec l'avis, un résumé et un constat par problème (règle, constat, suggestion).

            Ne modifie jamais le brouillon, ne le soumets pas et ne le publie pas : l'auteur corrige ou publie. Un avis « favorable » permet la publication immédiate sur le site : ne le donne que si le cours est prêt pour le public.
            TXT;
    }

    public static function qcmWritingPrompt(string $sujet, string $audience): string
    {
        return <<<TXT
            Prépare un QCM sur : « {$sujet} », pour le public « {$audience} ».

            Démarche :
            1. lire_regles avec les identifiants QCM-01 à QCM-05, RED-01 et DON-04, et lis-les en entier.
            2. lister_catalogue_qcm : choisis le thème et le niveau adaptés. Si le thème ou le niveau manque, propose un nom pertinent à la personne et demande son accord ; après accord, utilise creer_theme_qcm et/ou creer_niveau_qcm, puis relis lister_catalogue_qcm pour récupérer les identifiants. Un nom déjà existant renvoie son identifiant sans créer de doublon. Ne bloque pas la création du QCM pour une entrée de catalogue manquante.
            3. Écris une seule question claire, de moins de 20 mots (QCM-03), avec « vous » pour le public « apprendre ».
            4. Propose de 2 à 6 réponses, dont UNE seule correcte (QCM-01). Les réponses fausses sont des erreurs que les gens font vraiment, sans piège ni « toutes les réponses » (QCM-02).
            5. Écris l'explication générale : pourquoi la bonne réponse est juste. Ajoute pour chaque réponse fausse pourquoi elle ne convient pas (QCM-04). Ton bienveillant, sans reproche.
            6. Données : uniquement fictives (DON-04) — « Mme Exemple », adresses en @example.com, numéros écrits avec des X.
            7. creer_brouillon_qcm, puis verifier_brouillon_qcm. Un contrôle bloquant refuse l'enregistrement : corrige et recommence. Traite les avertissements.
            8. Termine par un compte rendu : identifiant du brouillon de QCM, thème, niveau, avertissements restants. Demande ensuite une relecture avec l'AUTRE connecteur (par exemple ChatGPT si tu es Claude) et la consigne « relire_qcm ».
            9. Après un avis favorable : publier_qcm, puis donne l'adresse publique et le thème et le niveau à choisir pour retrouver le QCM. Avec des réserves : modifier_brouillon_qcm, puis redemande une relecture.

            Ne publie jamais sans relecture favorable d'un autre connecteur. Le site n'enregistre aucune note : ne promets pas de suivi des résultats.
            TXT;
    }

    public static function qcmReviewPrompt(int $id): string
    {
        return <<<TXT
            Tu es l'agent relecteur. Relis le brouillon de QCM {$id} sans le modifier.

            1. voir_brouillon_qcm {$id}, puis verifier_brouillon_qcm {$id} pour les contrôles automatiques.
            2. lire_regles avec les identifiants QCM-01 à QCM-05, RED-01 et DON-04.
            3. Grille de relecture (cite l'identifiant de règle pour chaque constat) :
               - QCM-01 : la bonne réponse est juste sans discussion ; aucune réponse fausse n'est défendable.
               - QCM-02 : les réponses fausses sont plausibles, sans piège, de forme proche de la bonne.
               - QCM-03 et RED-01 : la question est courte, claire et utile pour le public visé.
               - QCM-04 : l'explication dit pourquoi ; chaque réponse fausse a son explication ; le ton est bienveillant.
               - DON-04 et QCM-05 : uniquement des données fictives.
               - Le thème et le niveau conviennent à la question.
            4. Avis :
               - « defavorable » si la bonne réponse est fausse ou discutable, ou si une donnée n'est pas fictive ;
               - « reserves » s'il reste des corrections de rédaction, de réponses fausses ou d'explication ;
               - « favorable » sinon.
            5. enregistrer_relecture_qcm avec l'avis, un résumé et un constat par problème (règle, constat, suggestion).

            Ne modifie jamais le QCM et ne le publie pas : l'auteur corrige ou publie. Un avis « favorable » permet la publication immédiate sur le site : ne le donne que si le QCM est prêt pour le public.
            TXT;
    }
}
