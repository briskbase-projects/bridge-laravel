<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when Bridge sends a `payment.succeeded` webhook event.
 *
 * Usage in your app:
 *
 *   Event::listen(BridgePaymentSucceeded::class, function ($event) {
 *       $payment = TenantPayment::find($event->externalReference);
 *       $payment->update(['status' => 'approved', 'subscription_starts_at' => now()]);
 *   });
 */
final class BridgePaymentSucceeded
{
    use Dispatchable;

    public function __construct(
        /** The unique webhook event ID (use for idempotency). */
        public readonly string $eventId,

        /** The checkout session ID on Bridge. */
        public readonly string $sessionId,

        /** Your internal reference passed when creating the checkout session. */
        public readonly string $externalReference,

        /** Amount in minor units. */
        public readonly int $amount,

        /** ISO 4217 currency code. */
        public readonly string $currency,

        /** Full raw webhook payload for fields not covered above. */
        public readonly array $payload,
    ) {}
}
