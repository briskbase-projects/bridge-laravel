<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Http;

use Briskbase\Bridge\Data\CheckoutSession;
use Briskbase\Bridge\Exceptions\BridgeException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Low-level signed HTTP client for Briskbase Bridge.
 *
 * All requests are HMAC-signed per the Bridge canonical-request spec:
 *   METHOD\nPATH\n\nTIMESTAMP\nNONCE\nSHA256(body)
 *
 * Never instantiate directly — use the Bridge facade or inject via DI.
 */
final class BridgeClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $keyId,
        private readonly string $secret,
        private readonly int $timeout,
        private readonly string $defaultCurrency,
        private readonly string $defaultCountry,
        private readonly bool $verifySsl = true,
    ) {}

    /**
     * Create a hosted checkout session.
     *
     * @param  int     $amount            Amount in minor units (e.g. 150000 = PKR 1500.00)
     * @param  string  $externalReference Your internal order/payment ID (used to match the webhook)
     * @param  string  $successUrl        Where to redirect after payment
     * @param  string  $cancelUrl         Where to redirect on cancellation
     * @param  string  $purpose           'subscription' | 'addon' | 'license' | 'one_time'
     * @param  string|null $idempotencyKey Reuse the same key when retrying the same purchase
     * @return CheckoutSession
     */
    public function createCheckoutSession(
        int $amount,
        string $externalReference,
        string $successUrl,
        string $cancelUrl,
        string $purpose = 'one_time',
        ?string $idempotencyKey = null,
        string $currency = '',
        string $country = '',
    ): CheckoutSession {
        $payload = [
            'amount' => $amount,
            'currency' => $currency ?: $this->defaultCurrency,
            'country' => $country ?: $this->defaultCountry,
            'purpose' => $purpose,
            'external_reference' => $externalReference,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        $response = $this->request(
            method: 'POST',
            path: '/api/v1/checkout-sessions',
            payload: $payload,
            idempotencyKey: $idempotencyKey ?? (string) Str::uuid(),
        );

        return new CheckoutSession(
            id: $response['id'],
            checkoutUrl: $response['checkout_url'],
            status: $response['status'],
            externalReference: $response['external_reference'] ?? $externalReference,
            raw: $response,
        );
    }

    /**
     * Retrieve a checkout session by ID.
     */
    public function getCheckoutSession(string $sessionId): CheckoutSession
    {
        $response = $this->request('GET', "/api/v1/checkout-sessions/{$sessionId}");

        return new CheckoutSession(
            id: $response['id'],
            checkoutUrl: $response['checkout_url'] ?? '',
            status: $response['status'],
            externalReference: $response['external_reference'] ?? '',
            raw: $response,
        );
    }

    /**
     * Verify credentials and return product info.
     */
    public function whoami(): array
    {
        return $this->request('GET', '/api/v1/whoami');
    }

    /**
     * Send a signed request to Bridge and return the decoded JSON body.
     *
     * @throws BridgeException on HTTP 4xx/5xx or connection failure
     */
    public function request(string $method, string $path, array $payload = [], ?string $idempotencyKey = null): array
    {
        $body = $payload === [] ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $timestamp = (string) time();
        $nonce = bin2hex(random_bytes(16));

        $canonical = implode("\n", [
            strtoupper($method),
            $path,
            '',
            $timestamp,
            $nonce,
            hash('sha256', (string) $body),
        ]);

        $signature = hash_hmac('sha256', $canonical, $this->secret);

        $headers = [
            'X-Briskbase-Key-Id' => $this->keyId,
            'X-Briskbase-Timestamp' => $timestamp,
            'X-Briskbase-Nonce' => $nonce,
            'X-Briskbase-Signature' => $signature,
            'Accept' => 'application/json',
        ];

        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        try {
            $http = Http::withHeaders($headers)->timeout($this->timeout);

            if (! $this->verifySsl) {
                $http = $http->withoutVerifying();
            }

            $response = $payload === []
                ? $http->send($method, $this->baseUrl . $path)
                : $http->withBody((string) $body, 'application/json')->send($method, $this->baseUrl . $path);
        } catch (ConnectionException $e) {
            throw new BridgeException('Bridge connection failed: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            $msg = $response->json('message') ?? $response->json('error') ?? 'HTTP ' . $response->status();
            throw new BridgeException("Bridge API error [{$response->status()}]: {$msg}");
        }

        return $response->json() ?? [];
    }
}
