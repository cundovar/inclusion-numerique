<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée ou met à jour le compte du responsable éditorial.
 * Le site n'a pas d'inscription : c'est le seul moyen d'ouvrir l'accès au back-office.
 */
#[AsCommand(name: 'app:create-admin', description: 'Crée ou met à jour un compte administrateur du back-office')]
class CreateAdminCommand extends Command
{
    private const MIN_PASSWORD_LENGTH = 12;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, "Nom d'utilisateur");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = trim((string) $input->getArgument('username'));
        if ($username === '') {
            $io->error("Le nom d'utilisateur est vide.");

            return Command::INVALID;
        }

        // Mot de passe saisi sans écho pour qu'il n'apparaisse ni à l'écran ni dans l'historique du shell
        $password = (string) $io->askHidden('Mot de passe (12 caractères minimum)');
        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $io->error(sprintf('Le mot de passe doit contenir au moins %d caractères.', self::MIN_PASSWORD_LENGTH));

            return Command::INVALID;
        }

        $user = $this->userRepository->findByUsername($username);
        $created = $user === null;
        $user ??= (new User())->setUsername($username);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->userRepository->save($user);

        $io->success(sprintf('Compte administrateur « %s » %s.', $username, $created ? 'créé' : 'mis à jour'));

        return Command::SUCCESS;
    }
}
