<?php

namespace App\Services\Carriers\Contracts;

/**
 * Contract every carrier gateway adapter implements.
 */
interface CarrierAdapterInterface
{
    /** Carrier code (config key). */
    public function code(): string;

    /** Display name. */
    public function name(): string;

    /** Whether credentials are configured (env presence — never values). */
    public function configured(): bool;

    /**
     * Normalized tracking: ['carrier','number','status','events'=>[['at','location','description']]]
     * Degrades to mock data when unconfigured/mock mode.
     */
    public function track(string $number): array;

    /** Lightweight credential/connectivity probe (mock-aware). */
    public function ping(): array;
}
