<?php

namespace App\Tests\Controller;

use App\Tests\Support\UtilisateurTestTrait;
use App\Tests\Support\VehiculeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VehiculeControllerTest extends WebTestCase
{
    use UtilisateurTestTrait;
    use VehiculeTestTrait;

    public function testCatalogueAccueilAccessiblePubliquement(): void
    {
        $client = static::createClient();
        $vehicule = $this->creerVehicule();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $vehicule->getMarque());
    }

    public function testListeAdminVehiculesRedirigeVersLoginPourAnonyme(): void
    {
        $client = static::createClient();

        $client->request('GET', '/vehicule');

        $this->assertResponseRedirects('/login');
    }

    public function testCreationVehiculeRedirigeVersLoginPourAnonyme(): void
    {
        $client = static::createClient();

        $client->request('GET', '/vehicule/new');

        $this->assertResponseRedirects('/login');
    }

    public function testCreationVehiculeInterditeAUnUtilisateurNonAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->creerUtilisateur(['ROLE_USER']));

        $client->request('GET', '/vehicule/new');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreationVehiculeAutoriseeAUnAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->creerUtilisateur(['ROLE_ADMIN']));

        $client->request('GET', '/vehicule/new');

        $this->assertResponseIsSuccessful();
    }
}
