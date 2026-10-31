<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Entity\User;
use App\Service\Learner\LearnerContentService;
use App\Service\Learner\LearnerQcmSessionService;
use App\Service\Learner\LaboratoryCatalogue;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;
use Mcp\Schema\Tool;
use Mcp\Schema\ToolAnnotations;
use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Lecture du catalogue propre au compte apprenant autorisant le connecteur. */
final class LearnerMcpServer
{
    public const NAME = 'devdoc-apprenant';
    public const VERSION = '0.1.0';

    public function __construct(
        private LearnerContentService $content,
        private LearnerQcmSessionService $qcm,
        private LaboratoryCatalogue $laboratories,
        private LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%/var/mcp-sessions')] private string $sessionDir,
        #[Autowire('%app.module_laboratoires%')] private bool $laboratoriesEnabled = false,
    ) {}

    public function build(User $learner): Server
    {
        $builder = Server::builder()
            ->setServerInfo(self::NAME, self::VERSION, 'Retrouver les cours publiés accessibles à la personne connectée.')
            ->setInstructions("Assistant apprenant de DevDoc. Utilisez les cours publiés accessibles au seul compte qui vous a autorisé. Ne demandez ni adresse e-mail ni données personnelles. Les résultats de recherche sont indicatifs : lisez le cours avant de le résumer.")
            ->setSession(new FileSessionStore($this->sessionDir))
            ->setLogger($this->logger);
        $readOnly = new ToolAnnotations(readOnlyHint: true, openWorldHint: false);

        $builder->add(
            new Tool('lister_parcours', 'Lister les parcours accessibles', self::schema([]), 'Parcours visibles pour ce compte, publics ou attribués à la personne.', $readOnly),
            new CallbackTool(fn (array $args) => self::result($this->content->parcours($learner))),
        );
        // Module laboratoires : outils proposés seulement si le site l'active
        if ($this->laboratoriesEnabled) {
            $builder->add(new Tool('lister_laboratoires', 'Lister les laboratoires locaux', self::schema(['niveau'=>['type'=>'string','enum'=>['debutant','intermediaire','avance']]]), 'Propose des simulations locales. Elles ne contactent pas de service d’IA et n’enregistrent aucun résultat.', $readOnly), new CallbackTool(fn(array $args)=>self::result($this->laboratories->list(isset($args['niveau'])?(string)$args['niveau']:null))));
            $builder->add(new Tool('voir_laboratoire', 'Voir les consignes d’un laboratoire', self::schema(['slug'=>['type'=>'string','minLength'=>1]], ['slug']), 'Retourne les objectifs et consignes d’un laboratoire local, sans exécuter de workflow.', $readOnly), new CallbackTool(fn(array $args)=>self::result($this->laboratories->find((string)$args['slug'])??['erreur'=>'Laboratoire introuvable.'])));
        }
        // Propre à DevDoc : sans cette liste, l'apprenant ne connaît pas les identifiants à passer à demarrer_qcm
        $builder->add(
            new Tool('lister_qcm', 'Lister les QCM', self::schema([
                'themeId' => ['type' => 'integer', 'minimum' => 1],
            ]), 'QCM accessibles à ce compte, avec leur thème et leur niveau, pour en démarrer un.', $readOnly),
            new CallbackTool(fn (array $args) => self::result($this->qcm->list($learner, isset($args['themeId']) ? (int) $args['themeId'] : null))),
        );
        $builder->add(
            new Tool('demarrer_qcm', 'Démarrer un QCM', self::schema(['id' => ['type' => 'integer', 'minimum' => 1]], ['id']), 'Retourne la question et les choix d’un QCM accessible. Les corrections restent cachées jusqu’à la réponse.', $readOnly),
            new CallbackTool(fn (array $args) => self::result($this->qcm->start($learner, (int) $args['id']) ?? ['erreur' => 'QCM introuvable.'])),
        );
        $builder->add(
            new Tool('repondre_qcm', 'Répondre au QCM', self::schema(['session' => ['type' => 'string', 'minLength' => 20], 'choixId' => ['type' => 'integer', 'minimum' => 1]], ['session', 'choixId']), 'Vérifie un choix pour la session reçue. Le résultat n’est pas enregistré.', $readOnly),
            new CallbackTool(fn (array $args) => self::result($this->qcm->submit($learner, (string) $args['session'], (int) $args['choixId']) ?? ['erreur' => 'Session ou choix introuvable.'])),
        );
        $builder->add(
            new Tool('rechercher_cours', 'Rechercher des passages cités', self::schema([
                'recherche' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 120],
            ], ['recherche']), 'Recherche sémantique dans les passages des cours publiés accessibles à la personne. Chaque résultat contient une citation à utiliser dans la réponse. En indisponibilité technique, le site utilise la recherche MySQL locale. Renvoie au plus dix résultats.', $readOnly),
            new CallbackTool(fn (array $args) => self::result($this->content->rechercher($learner, (string) $args['recherche']))),
        );
        $builder->add(
            new Tool('lire_cours', 'Lire un cours accessible', self::schema([
                'id' => ['type' => 'integer', 'minimum' => 1],
            ], ['id']), 'Lit un cours publié accessible à la personne. Un cours absent, non publié ou attribué à quelqu’un d’autre reste introuvable.', $readOnly),
            new CallbackTool(fn (array $args) => self::result($this->content->course($learner, (int) $args['id']) ?? ['erreur' => 'Cours introuvable.'])),
        );

        return $builder->build();
    }

    /** @param array<string, mixed> $properties @param list<string> $required */
    private static function schema(array $properties, array $required = []): array
    {
        return ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false];
    }

    private static function result(mixed $value): CallToolResult
    {
        return new CallToolResult([new TextContent((string) json_encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES))]);
    }
}
