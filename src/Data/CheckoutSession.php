<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Data;

final readonly class CheckoutSession
{
    public function __construct(
        public string $id,
        public string $checkoutUrl,
        public string $status,
        public string $externalReference,
        public array $raw = [],
    ) {}

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
