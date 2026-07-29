<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
#[IsGranted('ROLE_USER')]
final class PaiementController extends AbstractController
{
    public function __construct(private readonly string $stripeSecretKey)
    {
    }

    #[Route('/{id}/payer', name: 'app_reservation_payer', methods: ['GET'])]
    public function payer(Reservation $reservation): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($reservation->isPaye()) {
            return $this->redirectToRoute('app_reservation_index');
        }

        $vehicule = $reservation->getVehicule();
        $nombreJours = max(1, $reservation->getDateDebut()->diff($reservation->getDateFin())->days);
        $montantCentimes = (int) round($vehicule->getPrixJour() * $nombreJours * 100);

        Stripe::setApiKey($this->stripeSecretKey);

        $successUrl = $this->generateUrl(
            'app_reservation_payer_succes',
            ['id' => $reservation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ).'?session_id={CHECKOUT_SESSION_ID}';

        $session = Session::create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $montantCentimes,
                    'product_data' => [
                        'name' => sprintf('Location %s %s (%d jour(s))', $vehicule->getMarque(), $vehicule->getModele(), $nombreJours),
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $this->generateUrl('app_reservation_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/{id}/payer/succes', name: 'app_reservation_payer_succes', methods: ['GET'])]
    public function succes(Reservation $reservation, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $session = Session::retrieve($request->query->getString('session_id'));

        if ('paid' === $session->payment_status) {
            $reservation->setPaye(true);
            $entityManager->flush();
            $this->addFlash('success', 'Paiement confirmé, merci !');
        } else {
            $this->addFlash('error', 'Le paiement n\'a pas pu être confirmé.');
        }

        return $this->redirectToRoute('app_reservation_index');
    }
}
