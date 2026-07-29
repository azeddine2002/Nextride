<?php

namespace App\Tests\Controller;

use App\Tests\Support\ReservationTestTrait;
use App\Tests\Support\UtilisateurTestTrait;
use App\Tests\Support\VehiculeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminReservationControllerTest extends WebTestCase
{
    use UtilisateurTestTrait;
    use VehiculeTestTrait;
    use ReservationTestTrait;

    public function testAccesAnonymeRedirigeVersLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/reservations');

        $this->assertResponseRedirects('/login');
    }

    public function testInterditAUnUtilisateurNonAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->creerUtilisateur(['ROLE_USER']));

        $client->request('GET', '/admin/reservations');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminVoitToutesLesReservations(): void
    {
        $client = static::createClient();
        $utilisateur = $this->creerUtilisateur(['ROLE_USER']);
        $vehicule = $this->creerVehicule();
        $this->creerReservation($utilisateur, $vehicule);

        $client->loginUser($this->creerUtilisateur(['ROLE_ADMIN']));
        $client->request('GET', '/admin/reservations');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $vehicule->getMarque());
    }

    public function testAdminPeutAnnulerUneReservation(): void
    {
        $client = static::createClient();
        $utilisateur = $this->creerUtilisateur(['ROLE_USER']);
        $vehicule = $this->creerVehicule();
        $reservation = $this->creerReservation($utilisateur, $vehicule);

        $client->loginUser($this->creerUtilisateur(['ROLE_ADMIN']));
        $crawler = $client->request('GET', '/admin/reservations');

        $form = $crawler->selectButton('Annuler')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/admin/reservations');
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'annulee');
    }
}
