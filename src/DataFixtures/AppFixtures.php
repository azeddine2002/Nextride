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
            [
                'Renault', 'Clio 5', CategorieVehicule::CITADINE, 39.0,
                'Citadine polyvalente et économique, idéale pour la ville comme les longs trajets. '
                .'Boîte manuelle 5 vitesses, moteur essence sobre, 5 places. Parfaite pour un premier '
                .'contact avec la conduite ou un usage quotidien simple.',
            ],
            [
                'Peugeot', '208', CategorieVehicule::CITADINE, 42.0,
                'Citadine moderne au look affirmé, dotée du cockpit numérique Peugeot i-Cockpit. '
                .'Disponible en boîte automatique EAT8, confortable en ville comme sur autoroute. '
                .'5 places, coffre de 311 litres.',
            ],
            [
                'SEAT', 'Arona', CategorieVehicule::SUV, 55.0,
                'SUV urbain compact, position de conduite surélevée et bonne visibilité. Boîte manuelle '
                .'6 vitesses, coffre modulable de 400 litres. Un bon compromis entre praticité d\'un SUV '
                .'et agilité d\'une citadine.',
            ],
            [
                'Volkswagen', 'T-Cross', CategorieVehicule::SUV, 62.0,
                'SUV compact spacieux et polyvalent, banquette arrière coulissante pour optimiser le '
                .'coffre. Boîte automatique DSG 7 rapports, finitions soignées. Idéal pour les familles '
                .'ou les week-ends prolongés.',
            ],
            [
                'BMW', 'Série 3', CategorieVehicule::LUXE_SPORTIVE, 95.0,
                'Berline sportive et élégante, propulsion arrière et châssis dynamique signés BMW. '
                .'Boîte automatique 8 rapports, intérieur premium avec système iDrive. Le compromis '
                .'parfait entre confort haut de gamme et plaisir de conduite.',
            ],
            [
                'Audi', 'RS3', CategorieVehicule::LUXE_SPORTIVE, 180.0,
                'Compacte sportive à hautes performances, moteur 5 cylindres turbo et transmission '
                .'intégrale quattro. Boîte automatique S tronic à double embrayage, 0 à 100 km/h en '
                .'moins de 4 secondes. Une expérience de conduite premium et sensationnelle.',
            ],
        ];

        foreach ($vehicules as [$marque, $modele, $categorie, $prixJour, $description]) {
            $vehicule = new Vehicule();
            $vehicule->setMarque($marque);
            $vehicule->setModele($modele);
            $vehicule->setCategorie($categorie);
            $vehicule->setPrixJour($prixJour);
            $vehicule->setDisponible(true);
            $vehicule->setDescription($description);
            $manager->persist($vehicule);
        }

        $manager->flush();
    }
}
