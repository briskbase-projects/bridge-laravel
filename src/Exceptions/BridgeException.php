<?php

declare(strict_types=1);

namespace Briskbase\Bridge\Exceptions;

use RuntimeException;

class BridgeException extends RuntimeException
{
    /** HTTP status of the Bridge response, when there was one. */
    public ?int $httpStatus = null;

    /** Bridge's machine-readable error code (e.g. payment_not_supported, plan_not_available, invalid_state). */
    public ?string $errorCode = null;
}
