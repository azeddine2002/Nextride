<?php

namespace App\Tests\Controller;

use App\Tests\Support\UtilisateurTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use UtilisateurTestTrait;

    public function testConnexionAvecIdentifiantsValides(): void
    {
        $client = static::createClient();
        $utilisateur = $this->creerUtilisateur();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            '_username' => $utilisateur->getEmail(),
            '_password' => 'Test1234!',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/');
    }

    public function testConnexionAvecMauvaisMotDePasseEchoue(): void
    {
        $client = static::createClient();
        $utilisateur = $this->creerUtilisateur();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            '_username' => $utilisateur->getEmail(),
            '_password' => 'MauvaisMotDePasse',
        ]);
        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }
}
