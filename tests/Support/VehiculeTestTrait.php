<?php

namespace App\Tests\Support;

use App\Entity\Vehicule;
use App\Enum\CategorieVehicule;

trait VehiculeTestTrait
{
    private function creerVehicule(bool $disponible = true): Vehicule
    {
        $em = static::getContainer()->get('doctrine')->getManager();

        $vehicule = new Vehicule();
        $vehicule->setMarque('Renault');
        $vehicule->setModele('Clio');
        $vehicule->setCategorie(CategorieVehicule::CITADINE);
        $vehicule->setPrixJour(35.0);
        $vehicule->setDisponible($disponible);
        $vehicule->setImage('clio.jpg');

        $em->persist($vehicule);
        $em->flush();

        return $vehicule;
    }
}
