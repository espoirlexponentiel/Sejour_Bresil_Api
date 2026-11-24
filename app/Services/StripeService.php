<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeService
{
    private string $publishableKey;
    private string $secretKey;

    public function __construct()
    {
        $this->publishableKey = (string) config('services.stripe.key');

        $secret = (string) config('services.stripe.secret');
        if (empty($secret) || str_starts_with($secret, '$')) {
            $secret = (string) env('STRIPE_SECRET_KEY', '');
        }
        if ((empty($secret) || str_starts_with($secret, '$')) && env('STRIPE_SECRET_KEY_B64')) {
            $secret = (string) base64_decode((string) env('STRIPE_SECRET_KEY_B64'));
        }
        $this->secretKey = $secret;
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * Create a Stripe PaymentIntent for a card payment.
     */
    public function createPaymentIntent(
        int $amountCents,
        string $currency = 'brl',
        array $metadata = [],
        ?string $receiptEmail = null,
        ?string $description = null
    ): array {
        if (!$this->secretKey) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $payload = [
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'payment_method_types' => ['card'],
        ];

        if ($receiptEmail) {
            $payload['receipt_email'] = $receiptEmail;
        }
        if ($description) {
            $payload['description'] = $description;
        }
        if (!empty($metadata)) {
            $payload['metadata'] = $metadata;
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->timeout(20)
            ->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->post('https://api.stripe.com/v1/payment_intents', $payload);

        $json = $response->json();

        if (!$response->successful() || empty($json['client_secret'])) {
            $errMsg = $json['error']['message'] ?? 'Erreur Stripe lors de la création du paiement.';
            throw new RuntimeException($errMsg);
        }

        return $json;
    }

    /**
     * Retrieve and verify a Stripe PaymentIntent by ID.
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        if (!$this->secretKey) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->timeout(20)
            ->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->get("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}", [
                'expand' => ['latest_charge'],
            ]);

        $json = $response->json();

        if (!$response->successful() || empty($json['id'])) {
            $errMsg = $json['error']['message'] ?? 'Paiement Stripe introuvable.';
            throw new RuntimeException($errMsg);
        }

        return $json;
    }
}

