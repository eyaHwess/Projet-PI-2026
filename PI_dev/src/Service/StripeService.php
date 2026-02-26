<?php

namespace App\Service;

use App\Entity\Session;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StripeService
{
    private const CURRENCY = 'usd';
    private StripeClient $client;

    public function __construct(
        #[Autowire(env: 'STRIPE_SECRET_KEY')]
        private string $secretKey,
    ) {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $this->client = new StripeClient($this->secretKey);
    }

    private function getClient(): StripeClient
    {
        return $this->client;
    }

    /**
     * Crée une session Stripe Checkout pour une session de coaching.
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Session $session, string $successUrl, string $cancelUrl): StripeCheckoutSession
    {
        $price = $session->getPrice();
        if ($price === null || $price <= 0) {
            $price = $session->getCoachingRequest()?->getBudget() ?? 50.0;
        }

        // Stripe attend le montant en centimes
        $amountInCents = (int) round($price * 100);
        if ($amountInCents < 50) {
            $amountInCents = 50; // Stripe minimum 0.50 €
        }

        $stripe = $this->getClient();

        return $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => self::CURRENCY,
                        'product_data' => [
                            'name' => sprintf('Session de coaching #%d', $session->getId()),
                            'description' => sprintf(
                                'Coaching avec %s - Durée : %d min',
                                $session->getCoachingRequest()?->getCoach()?->getFirstName() ?? 'Coach',
                                $session->getDuration() ?? 60
                            ),
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $session->getId(),
            'metadata' => [
                'session_id' => (string) $session->getId(),
            ],
        ]);
    }
}
