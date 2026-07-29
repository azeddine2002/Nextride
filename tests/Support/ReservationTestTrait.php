<?php

namespace App\Tests\Support;

use App\Entity\Reservation;
use App\Entity\Utilisateur;
use App\Entity\Vehicule;
use App\Enum\StatutReservation;

trait ReservationTestTrait
{
    private function creerReservation(Utilisateur $utilisateur, Vehicule $vehicule, StatutReservation $statut = StatutReservation::EN_COURS): Reservation
    {
        $em = static::getContainer()->get('doctrine')->getManager();

        $reservation = new Reservation();
        $reservation->setUtilisateur($utilisateur);
        $reservation->setVehicule($vehicule);
        $reservation->setDateDebut(new \DateTimeImmutable('2026-08-01'));
        $reservation->setDateFin(new \DateTimeImmutable('2026-08-05'));
        $reservation->setStatut($statut);

        $em->persist($reservation);
        $em->flush();

        return $reservation;
    }
}
