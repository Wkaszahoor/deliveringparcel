<?php

namespace App\Services\Payments;

use RuntimeException;

/**
 * Agent PM — payment domain exception.
 *
 * $message is CUSTOMER-SAFE copy; technical details ride on
 * $technical and are only ever written to the payments log channel.
 */
class PaymentException extends RuntimeException
{
    public ?string $technical;

    public function __construct(string $friendly, ?string $technical = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($friendly, $code, $previous);
        $this->technical = $technical;
    }
}
