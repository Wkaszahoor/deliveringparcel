<?php

namespace App\Services;

/**
 * Detects the likely carrier from a tracking number's shape.
 */
class TrackingNumberParser
{
    /** @return array<int, array{carrier: string, confidence: int}> best first */
    public static function allMatches(string $number): array
    {
        $n = strtoupper(preg_replace('/\s+/', '', trim($number)));
        $out = [];

        // UPS: 1Z + 16 alphanumerics
        if (preg_match('/^1Z[0-9A-Z]{16}$/', $n)) {
            $out[] = ['carrier' => 'ups', 'confidence' => 95];
        }
        // FedEx: 12 or 15 or 20 digits
        if (preg_match('/^\d{12}$/', $n) || preg_match('/^\d{15}$/', $n) || preg_match('/^\d{20}$/', $n)) {
            $out[] = ['carrier' => 'fedex', 'confidence' => 80];
        }
        // DHL express: 10 digits
        if (preg_match('/^\d{10}$/', $n)) {
            $out[] = ['carrier' => 'dhl', 'confidence' => 80];
        }
        // Correos (Spain): RR\d{9}ES or P\d{9}ES style
        if (preg_match('/^(RR|P[QO])\d{9}ES$/i', $n)) {
            $out[] = ['carrier' => 'correos', 'confidence' => 90];
        }
        // Royal Mail: 2 letters + 9 digits + GB + 2 letters
        if (preg_match('/^[A-Z]{2}\d{9}GB[A-Z]{2}$/', $n)) {
            $out[] = ['carrier' => 'royalmail', 'confidence' => 90];
        }
        // Aggregators accept anything
        $out[] = ['carrier' => '17track', 'confidence' => 50];
        $out[] = ['carrier' => 'aftership', 'confidence' => 50];

        usort($out, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $out;
    }

    public static function detect(string $number): array
    {
        return self::allMatches($number)[0] ?? ['carrier' => '17track', 'confidence' => 50];
    }
}
