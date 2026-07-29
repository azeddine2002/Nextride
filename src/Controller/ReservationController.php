<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Utilisateur;
use App\Entity\Vehicule;
use App\Enum\StatutReservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
#[IsGranted('ROLE_USER')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(
                ['utilisateur' => $utilisateur],
                ['dateDebut' => 'DESC']
            ),
        ]);
    }

    #[Route('/nouvelle/{id}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(
        Vehicule $vehicule,
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
    ): Response {
        if (!$vehicule->isDisponible()) {
            $this->addFlash('error', 'Ce véhicule n\'est pas disponible à la location.');

            return $this->redirectToRoute('app_vehicule_show', ['id' => $vehicule->getId()]);
        }

        $reservation = new Reservation();
        $reservation->setVehicule($vehicule);

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($reservation->getDateFin() <= $reservation->getDateDebut()) {
                $this->addFlash('error', 'La date de fin doit être postérieure à la date de début.');

                return $this->render('reservation/new.html.twig', [
                    'vehicule' => $vehicule,
                    'form' => $form,
                ]);
            }

            if ($reservationRepository->hasOverlappingReservation($vehicule, $reservation->getDateDebut(), $reservation->getDateFin())) {
                $this->addFlash('error', 'Ce véhicule est déjà réservé sur cette période.');

                return $this->render('reservation/new.html.twig', [
                    'vehicule' => $vehicule,
                    'form' => $form,
                ]);
            }

            /** @var Utilisateur $utilisateur */
            $utilisateur = $this->getUser();

            $reservation->setUtilisateur($utilisateur);
            $reservation->setStatut(StatutReservation::EN_COURS);

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation confirmée.');

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/annuler', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('annuler'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $reservation->setStatut(StatutReservation::ANNULEE);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation annulée.');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
