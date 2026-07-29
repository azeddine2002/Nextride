<?php

namespace App\Tests\Controller;

use App\Tests\Support\UtilisateurTestTrait;
use App\Tests\Support\VehiculeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationControllerTest extends WebTestCase
{
    use UtilisateurTestTrait;
    use VehiculeTestTrait;

    public function testAccesAnonymeRedirigeVersLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/reservation');

        $this->assertResponseRedirects('/login');
    }

    public function testReservationVehiculeDisponible(): void
    {
        $client = static::createClient();
        $vehicule = $this->creerVehicule();
        $client->loginUser($this->creerUtilisateur());

        $crawler = $client->request('GET', '/reservation/nouvelle/'.$vehicule->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Confirmer la réservation')->form([
            'reservation[dateDebut]' => '2026-08-01',
            'reservation[dateFin]' => '2026-08-05',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/reservation');
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'en_cours');
    }

    public function testReservationChevauchanteRefusee(): void
    {
        $client = static::createClient();
        $vehicule = $this->creerVehicule();
        $client->loginUser($this->creerUtilisateur());

        $crawler = $client->request('GET', '/reservation/nouvelle/'.$vehicule->getId());
        $form = $crawler->selectButton('Confirmer la réservation')->form([
            'reservation[dateDebut]' => '2026-08-01',
            'reservation[dateFin]' => '2026-08-05',
        ]);
        $client->submit($form);

        $crawler = $client->request('GET', '/reservation/nouvelle/'.$vehicule->getId());
        $form = $crawler->selectButton('Confirmer la réservation')->form([
            'reservation[dateDebut]' => '2026-08-03',
            'reservation[dateFin]' => '2026-08-10',
        ]);
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'déjà réservé');
    }

    public function testVehiculeIndisponibleNePeutPasEtreReserve(): void
    {
        $client = static::createClient();
        $vehicule = $this->creerVehicule(disponible: false);
        $client->loginUser($this->creerUtilisateur());

        $client->request('GET', '/reservation/nouvelle/'.$vehicule->getId());

        $this->assertResponseRedirects('/vehicule/'.$vehicule->getId());
    }
}
