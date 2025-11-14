<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Medecin;
use App\Entity\Secretaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer des utilisateurs avec leurs profils',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Créer un profil Médecin
        $medecinProfile = new Medecin();
        $medecinProfile->setNom('Dupont');
        $medecinProfile->setPrenom('Jean');
        $medecinProfile->setSpecialite('Médecine Générale');
        $medecinProfile->setTelephone('0612345678');
        $medecinProfile->setEmail('medecin@cabinet.com');
        $this->em->persist($medecinProfile);

        // Créer un profil Secrétaire
        $secretaireProfile = new Secretaire();
        $secretaireProfile->setNom('Martin');
        $secretaireProfile->setPrenom('Marie');
        $secretaireProfile->setTelephone('0623456789');
        $secretaireProfile->setEmail('secretaire@cabinet.com');
        $this->em->persist($secretaireProfile);

        // Flush pour obtenir les IDs
        $this->em->flush();

        // Admin
        $admin = new User();
        $admin->setEmail('admin@cabinet.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        
        // Médecin avec profil associé
        $medecin = new User();
        $medecin->setEmail('medecin@cabinet.com');
        $medecin->setNom('Dupont');
        $medecin->setPrenom('Jean');
        $medecin->setRoles(['ROLE_MEDECIN']);
        $medecin->setPassword($this->passwordHasher->hashPassword($medecin, 'medecin123'));
        $medecin->setMedecin($medecinProfile);
        
        // Secrétaire avec profil associé
        $secretaire = new User();
        $secretaire->setEmail('secretaire@cabinet.com');
        $secretaire->setNom('Martin');
        $secretaire->setPrenom('Marie');
        $secretaire->setRoles(['ROLE_SECRETAIRE']);
        $secretaire->setPassword($this->passwordHasher->hashPassword($secretaire, 'secretaire123'));
        $secretaire->setSecretaire($secretaireProfile);

        $this->em->persist($admin);
        $this->em->persist($medecin);
        $this->em->persist($secretaire);
        $this->em->flush();

        $io->success('Utilisateurs créés avec succès!');
        $io->table(
            ['Email', 'Mot de passe', 'Rôle'],
            [
                ['admin@cabinet.com', 'admin123', 'ADMIN'],
                ['medecin@cabinet.com', 'medecin123', 'MEDECIN (avec profil)'],
                ['secretaire@cabinet.com', 'secretaire123', 'SECRETAIRE (avec profil)'],
            ]
        );

        return Command::SUCCESS;
    }
}