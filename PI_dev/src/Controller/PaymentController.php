<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/payment', name: 'app_payment_')]
class PaymentController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private SessionRepository $sessionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Crée une Stripe Checkout Session et redirige vers Stripe.
     */
    #[Route('/checkout/{id}', name: 'checkout', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function checkout(Session $session): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cr = $session->getCoachingRequest();
        if (!$cr || $cr->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette session.');
        }

        if ($session->isPaid()) {
            $this->addFlash('info', 'Cette session est déjà payée.');
            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        $successUrl = $this->generateUrl(
            'app_payment_success',
            ['id' => $session->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $cancelUrl = $this->generateUrl(
            'app_session_show',
            ['id' => $session->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        try {
            $stripeSession = $this->stripeService->createCheckoutSession($session, $successUrl, $cancelUrl);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création du paiement : ' . $e->getMessage());
            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        return $this->redirect($stripeSession->url, Response::HTTP_SEE_OTHER);
    }

    /**
     * Page de succès après paiement Stripe.
     * Met à jour paymentStatus de la session.
     */
    #[Route('/success/{id}', name: 'success', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function success(Session $session, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cr = $session->getCoachingRequest();
        if (!$cr || $cr->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette session.');
        }

        // Marquer comme payé (Stripe redirige vers success uniquement si paiement réussi)
        if (!$session->isPaid()) {
            $session->setPaymentStatus(Session::PAYMENT_STATUS_PAID);
            $session->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Paiement effectué avec succès ! Merci pour votre confiance.');

        return $this->render('payment/success.html.twig', [
            'session' => $session,
        ]);
    }
}
