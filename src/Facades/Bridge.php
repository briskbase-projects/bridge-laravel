<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Facades;

use Briskbase\Bridge\Data\CheckoutSession;
use Briskbase\Bridge\Http\BridgeClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CheckoutSession createCheckoutSession(int $amount, string $externalReference, string $successUrl, string $cancelUrl, string $purpose = 'one_time', ?string $idempotencyKey = null, string $currency = '', string $country = '')
 * @method static CheckoutSession getCheckoutSession(string $sessionId)
 * @method static array whoami()
 * @method static array request(string $method, string $path, array $payload = [], ?string $idempotencyKey = null)
 *
 * @see BridgeClient
 */
final class Bridge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BridgeClient::class;
    }
}
