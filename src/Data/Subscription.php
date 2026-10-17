<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Data;

/**
 * A subscription as Bridge reports it (API response or webhook `data.subscription`).
 */
final readonly class Subscription
{
    public function __construct(
        public string $id,
        /** pending | active | past_due | cancelled */
        public string $status,
        public string $externalReference,
        /** Where to send the customer to subscribe (only on the response that creates it). */
        public string $subscribeUrl,
        /** Will the next period be charged automatically? */
        public bool $autoRenew,
        /** Renewal is switched off: still paid and active until currentPeriodEnd. */
        public bool $cancelAtPeriodEnd,
        /** Paid-through date, ISO 8601, or null before the first charge. */
        public ?string $currentPeriodEnd,
        public int $cyclesPaid,
        public array $raw = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            externalReference: (string) ($data['external_reference'] ?? ''),
            subscribeUrl: (string) ($data['subscribe_url'] ?? ''),
            autoRenew: (bool) ($data['auto_renew'] ?? false),
            cancelAtPeriodEnd: (bool) ($data['cancel_at_period_end'] ?? false),
            currentPeriodEnd: isset($data['current_period_end']) ? (string) $data['current_period_end'] : null,
            cyclesPaid: (int) ($data['cycles_paid'] ?? 0),
            raw: $data,
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
