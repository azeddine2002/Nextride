<?php

namespace App\Tests\Controller;

use App\Tests\Support\ReservationTestTrait;
use App\Tests\Support\UtilisateurTestTrait;
use App\Tests\Support\VehiculeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUtilisateurControllerTest extends WebTestCase
{
    use UtilisateurTestTrait;
    use VehiculeTestTrait;
    use ReservationTestTrait;

    public function testAccesAnonymeRedirigeVersLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/utilisateurs');

        $this->assertResponseRedirects('/login');
    }

    public function testInterditAUnUtilisateurNonAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->creerUtilisateur(['ROLE_USER']));

        $client->request('GET', '/admin/utilisateurs');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminPeutSupprimerUnUtilisateur(): void
    {
        $client = static::createClient();
        $utilisateurASupprimer = $this->creerUtilisateur(['ROLE_USER']);
        $client->loginUser($this->creerUtilisateur(['ROLE_ADMIN']));

        $crawler = $client->request('GET', '/admin/utilisateurs');
        $this->assertSelectorTextContains('body', $utilisateurASupprimer->getEmail());

        $form = $crawler->filter('form[action="/admin/utilisateurs/'.$utilisateurASupprimer->getId().'/supprimer"] button')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/admin/utilisateurs');
        $client->followRedirect();
        $this->assertSelectorTextNotContains('body', $utilisateurASupprimer->getEmail());
    }

    public function testSuppressionUtilisateurSupprimeSesReservations(): void
    {
        $client = static::createClient();
        $utilisateurASupprimer = $this->creerUtilisateur(['ROLE_USER']);
        $vehicule = $this->creerVehicule();
        $this->creerReservation($utilisateurASupprimer, $vehicule);

        $client->loginUser($this->creerUtilisateur(['ROLE_ADMIN']));

        $crawler = $client->request('GET', '/admin/utilisateurs');
        $form = $crawler->filter('form[action="/admin/utilisateurs/'.$utilisateurASupprimer->getId().'/supprimer"] button')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/admin/utilisateurs');
    }

    public function testAdminNePeutPasSeSupprimerLuiMeme(): void
    {
        $client = static::createClient();
        $admin = $this->creerUtilisateur(['ROLE_ADMIN']);
        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/utilisateurs');

        $this->assertCount(0, $crawler->filter('form[action="/admin/utilisateurs/'.$admin->getId().'/supprimer"]'));
    }
}
