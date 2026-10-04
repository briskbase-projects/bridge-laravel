<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when Bridge sends a `payment.failed` or `payment.cancelled` webhook event.
 */
final class BridgePaymentFailed
{
    use Dispatchable;

    public function __construct(
        public readonly string $eventId,
        public readonly string $sessionId,
        public readonly string $externalReference,
        public readonly string $eventType,
        public readonly array $payload,
    ) {}
}
