<?php

namespace App\Controller;

use App\Enum\CategorieVehicule;
use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, VehiculeRepository $vehiculeRepository): Response
    {
        $recherche = $request->query->getString('q') ?: null;

        $categories = [];
        foreach ($request->query->all('categorie') as $valeur) {
            $categorie = CategorieVehicule::tryFrom((string) $valeur);
            if (null !== $categorie) {
                $categories[] = $categorie;
            }
        }

        $prixMaxParam = $request->query->get('prixMax');
        $prixMax = is_numeric($prixMaxParam) ? (float) $prixMaxParam : null;

        return $this->render('home/index.html.twig', [
            'vehicules' => $vehiculeRepository->rechercher($recherche, $categories, $prixMax),
            'recherche' => $recherche,
            'categoriesSelectionnees' => array_map(fn (CategorieVehicule $c) => $c->value, $categories),
            'prixMax' => $prixMax,
        ]);
    }
}
