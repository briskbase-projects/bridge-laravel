<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Events;

use Briskbase\Bridge\Data\Subscription;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired for every `subscription.*` webhook from Bridge:
 *
 *   subscription.activated       first successful charge: grant access
 *   subscription.renewed         a later successful charge: extend access
 *   subscription.payment_failed  a charge failed: warn the customer (status past_due)
 *   subscription.cancelled       billing stopped
 *   subscription.updated         auto-renewal switched on or off
 *
 * Usage:
 *
 *   Event::listen(BridgeSubscriptionEvent::class, function ($event) {
 *       $local = AppSubscription::where('bridge_subscription_id', $event->subscription->id)->first();
 *       ...
 *   });
 */
final class BridgeSubscriptionEvent
{
    use Dispatchable;

    public function __construct(
        /** The unique webhook event ID (use for idempotency). */
        public readonly string $eventId,

        /** subscription.activated | .renewed | .payment_failed | .cancelled | .updated */
        public readonly string $type,

        public readonly Subscription $subscription,

        /** The charge this event is about (activated, renewed and payment_failed only). */
        public readonly ?string $chargeId,

        /** Charge amount in minor units, when there is a charge. */
        public readonly ?int $chargeAmount,

        public readonly ?string $chargeCurrency,

        /** Full raw webhook payload for fields not covered above. */
        public readonly array $payload,
    ) {}

    public function is(string $type): bool
    {
        return $this->type === $type;
    }
}
