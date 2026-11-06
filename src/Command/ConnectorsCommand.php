<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\OAuthClient;
use App\Entity\OAuthGrant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Connecteurs MCP (claude.ai, ChatGPT) : liste et coupure des accès accordés, depuis le serveur.
 */
#[AsCommand(name: 'app:connecteurs', description: 'Liste les connecteurs MCP ou coupe leurs accès')]
final class ConnectorsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::OPTIONAL, 'list ou revoke', 'list')
            ->addArgument('connecteur', InputArgument::OPTIONAL, 'Identifiant du connecteur (début accepté, au moins 8 caractères) pour revoke');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $grants = $this->entityManager->getRepository(OAuthGrant::class);

        if ($input->getArgument('action') === 'revoke') {
            $prefix = (string) $input->getArgument('connecteur');
            $matches = array_values(array_filter(
                $this->entityManager->getRepository(OAuthClient::class)->findAll(),
                static fn (OAuthClient $client) => \strlen($prefix) >= 8 && str_starts_with($client->getClientId(), $prefix),
            ));
            if (\count($matches) !== 1) {
                $io->error('Indiquez le début (8 caractères au moins) de l\'identifiant d\'un seul connecteur : voir app:connecteurs list.');

                return Command::FAILURE;
            }
            $count = 0;
            foreach ($grants->findBy(['client' => $matches[0], 'revokedAt' => null]) as $grant) {
                $grant->revoke();
                ++$count;
            }
            $this->entityManager->flush();
            $io->success(sprintf('%d accès coupé(s) pour %s. Il faudra reconnecter ce connecteur pour l\'utiliser.', $count, $matches[0]->getAgentLabel()));

            return Command::SUCCESS;
        }

        if ($input->getArgument('action') !== 'list') {
            $io->error('Action attendue : list ou revoke.');

            return Command::INVALID;
        }

        $rows = [];
        foreach ($this->entityManager->getRepository(OAuthClient::class)->findBy([], ['id' => 'ASC']) as $client) {
            $active = array_filter($grants->findBy(['client' => $client]), static fn (OAuthGrant $grant) => $grant->isRefreshValid(new \DateTimeImmutable()));
            $rows[] = [
                $client->getAgentLabel(),
                $client->getClientId(),
                $client->getCreatedAt()->format('Y-m-d H:i'),
                $client->getLastUsedAt()?->format('Y-m-d H:i') ?? '—',
                implode(', ', array_unique(array_map(static fn (OAuthGrant $grant) => $grant->getUser()->getUserIdentifier(), $active))) ?: '—',
            ];
        }
        $io->table(['Agent', 'Identifiant', 'Enregistré', 'Dernier usage', 'Accès actifs pour'], $rows);

        return Command::SUCCESS;
    }
}
