<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Http;

use Briskbase\Bridge\Events\BridgePaymentFailed;
use Briskbase\Bridge\Events\BridgePaymentSucceeded;
use Briskbase\Bridge\Exceptions\WebhookSignatureException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Verifies and dispatches Briskbase Bridge webhook events.
 *
 * Register this in your routes/web.php (outside auth middleware, CSRF-exempt):
 *
 *   Route::post('/webhooks/bridge', \Briskbase\Bridge\Http\WebhookHandler::class);
 *
 * Add the route to your CSRF exclusion list in bootstrap/app.php or VerifyCsrfToken:
 *
 *   $middleware->validateCsrfTokens(except: ['/webhooks/bridge']);
 *
 * Then listen to the events fired by this handler:
 *
 *   Event::listen(BridgePaymentSucceeded::class, YourListener::class);
 */
final class WebhookHandler
{
    public function __invoke(Request $request): Response
    {
        $raw = $request->getContent();
        $timestamp = (string) $request->header('X-Briskbase-Timestamp', '');
        $signature = (string) $request->header('X-Briskbase-Signature', '');
        $secret = (string) config('bridge.webhook_secret');
        $tolerance = (int) config('bridge.webhook_tolerance', 300);

        $this->verify($raw, $timestamp, $signature, $secret, $tolerance);

        $payload = json_decode($raw, true) ?? [];
        $eventType = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];

        match ($eventType) {
            'payment.succeeded' => BridgePaymentSucceeded::dispatch(
                eventId: (string) ($payload['id'] ?? ''),
                sessionId: (string) ($data['id'] ?? ''),
                externalReference: (string) ($data['external_reference'] ?? ''),
                amount: (int) ($data['amount'] ?? 0),
                currency: (string) ($data['currency'] ?? ''),
                payload: $payload,
            ),
            'payment.failed', 'payment.cancelled' => BridgePaymentFailed::dispatch(
                eventId: (string) ($payload['id'] ?? ''),
                sessionId: (string) ($data['id'] ?? ''),
                externalReference: (string) ($data['external_reference'] ?? ''),
                eventType: $eventType,
                payload: $payload,
            ),
            default => null,
        };

        return response()->noContent();
    }

    private function verify(string $raw, string $timestamp, string $signature, string $secret, int $tolerance): void
    {
        if ($timestamp === '' || $signature === '') {
            throw new WebhookSignatureException('Missing Bridge signature headers.');
        }

        if (abs(time() - (int) $timestamp) > $tolerance) {
            throw new WebhookSignatureException('Bridge webhook timestamp is outside the tolerance window.');
        }

        $expected = 'v1=' . hash_hmac('sha256', $timestamp . '.' . $raw, $secret);

        if (! hash_equals($expected, $signature)) {
            throw new WebhookSignatureException('Bridge webhook signature mismatch.');
        }
    }
}
