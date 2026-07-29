<?php

namespace App\Tests\Controller;

use App\Entity\Vehicule;
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

    public function testCreationVehiculeAvecPhoto(): void
    {
        $client = static::createClient();
        $client->loginUser($this->creerUtilisateur(['ROLE_ADMIN']));

        // PNG 1x1 minimal encode en base64, pour ne pas dependre de l'extension GD
        $pngMinimal = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $cheminImage = sys_get_temp_dir().'/nextride-test-photo.png';
        file_put_contents($cheminImage, $pngMinimal);

        $crawler = $client->request('GET', '/vehicule/new');
        $form = $crawler->selectButton('Enregistrer')->form([
            'vehicule[marque]' => 'Renault',
            'vehicule[modele]' => 'Megane',
            'vehicule[categorie]' => 'citadine',
            'vehicule[prixJour]' => '40',
            'vehicule[description]' => 'Véhicule de test',
        ]);
        $form['vehicule[imageFile]']->upload($cheminImage);

        $client->submit($form);
        unlink($cheminImage);

        $this->assertResponseRedirects('/vehicule');

        /** @var Vehicule $vehicule */
        $vehicule = static::getContainer()->get('doctrine')->getRepository(Vehicule::class)
            ->findOneBy(['marque' => 'Renault', 'modele' => 'Megane']);

        $this->assertNotNull($vehicule->getImage());

        $cheminUploade = static::getContainer()->getParameter('vehicules_upload_dir').'/'.$vehicule->getImage();
        $this->assertFileExists($cheminUploade);
        unlink($cheminUploade);
    }
}
