<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Crée un compte administrateur pour /admin')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail du compte admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe du compte admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->error(sprintf('Un utilisateur avec l\'e-mail "%s" existe déjà.', $email));

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRole(User::ROLE_ADMIN);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Compte admin créé pour %s. Connecte-toi sur /admin/login.', $email));

        return Command::SUCCESS;
    }
}
