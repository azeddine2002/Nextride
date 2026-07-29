<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
final class AdminUtilisateurController extends AbstractController
{
    #[Route(name: 'app_admin_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('admin/utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findBy([], ['dateInscription' => 'DESC']),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($utilisateur === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');

            return $this->redirectToRoute('app_admin_utilisateur_index');
        }

        if ($this->isCsrfTokenValid('supprimer-utilisateur'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur et ses réservations supprimés.');
        }

        return $this->redirectToRoute('app_admin_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }
}
