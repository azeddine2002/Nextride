<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Enum\StatutReservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reservations')]
#[IsGranted('ROLE_ADMIN')]
final class AdminReservationController extends AbstractController
{
    #[Route(name: 'app_admin_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy([], ['dateDebut' => 'DESC']),
        ]);
    }

    #[Route('/{id}/annuler', name: 'app_admin_reservation_cancel', methods: ['POST'])]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('admin-annuler'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $reservation->setStatut(StatutReservation::ANNULEE);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation annulée.');
        }

        return $this->redirectToRoute('app_admin_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
