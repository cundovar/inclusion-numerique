<?php

namespace App\Command;

use App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-page-visits',
    description: 'Supprime automatiquement les visites de pages les plus anciennes au-delà d\'une limite',
)]
class CleanupPageVisitsCommand extends Command
{
    public function __construct(
        private UserPageVisitRepositoryInterface $visitRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Nombre maximum de visites à conserver', 200)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Afficher ce qui serait supprimé sans vraiment supprimer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $dryRun = $input->getOption('dry-run');

        $io->title('Nettoyage des visites de pages');
        $io->info(sprintf('Limite configurée : %d visites maximum', $limit));

        // Compter le nombre total de visites
        $totalVisits = $this->visitRepository->countAll();
        $io->comment(sprintf('Nombre total de visites actuellement : %d', $totalVisits));

        if ($totalVisits <= $limit) {
            $io->success(sprintf('Aucune suppression nécessaire. Total (%d) <= Limite (%d)', $totalVisits, $limit));
            return Command::SUCCESS;
        }

        // Calculer combien de visites doivent être supprimées
        $toDelete = $totalVisits - $limit;
        $io->warning(sprintf('Nombre de visites à supprimer : %d', $toDelete));

        // Récupérer les visites les plus anciennes
        $oldestVisits = $this->visitRepository->findOldest($toDelete);

        if ($dryRun) {
            $io->note('Mode DRY-RUN activé : aucune suppression ne sera effectuée');

            if (!empty($oldestVisits)) {
                $io->section('Visites qui seraient supprimées :');
                foreach ($oldestVisits as $visit) {
                    $io->text(sprintf(
                        '- [%d] %s - %s (%s)',
                        $visit->getId(),
                        $visit->getVisitedAt()->format('Y-m-d H:i:s'),
                        $visit->getPageTitle() ?? $visit->getPageUrl(),
                        $visit->getUser()->getUsername()
                    ));
                }
            }

            return Command::SUCCESS;
        }

        // Supprimer les visites
        $io->progressStart($toDelete);
        $deleted = 0;

        foreach ($oldestVisits as $visit) {
            $this->visitRepository->delete($visit);
            $deleted++;

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(sprintf(
            '%d visites supprimées avec succès. Visites restantes : %d',
            $deleted,
            $this->visitRepository->countAll()
        ));

        return Command::SUCCESS;
    }
}
