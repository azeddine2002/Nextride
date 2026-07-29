<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Vehicule;
use App\Enum\CategorieVehicule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Utilisateur();
        $admin->setEmail('admin@nextride.fr');
        $admin->setNom('Amari');
        $admin->setPrenom('Azeddine');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setDateInscription(new \DateTimeImmutable());
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);

        $client = new Utilisateur();
        $client->setEmail('client@nextride.fr');
        $client->setNom('Dupont');
        $client->setPrenom('Jean');
        $client->setRoles(['ROLE_USER']);
        $client->setDateInscription(new \DateTimeImmutable());
        $client->setPassword($this->passwordHasher->hashPassword($client, 'Client1234!'));
        $manager->persist($client);

        $vehicules = [
            ['Renault', 'Clio', CategorieVehicule::CITADINE, 35.0],
            ['Peugeot', '3008', CategorieVehicule::SUV, 65.0],
            ['Porsche', '911', CategorieVehicule::LUXE_SPORTIVE, 250.0],
        ];

        foreach ($vehicules as [$marque, $modele, $categorie, $prixJour]) {
            $vehicule = new Vehicule();
            $vehicule->setMarque($marque);
            $vehicule->setModele($modele);
            $vehicule->setCategorie($categorie);
            $vehicule->setPrixJour($prixJour);
            $vehicule->setDisponible(true);
            $vehicule->setImage('placeholder.jpg');
            $manager->persist($vehicule);
        }

        $manager->flush();
    }
}
