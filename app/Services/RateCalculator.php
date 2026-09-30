<?php

namespace App\Services;

use App\Models\RateRule;
use App\Models\RateSurcharge;
use App\Models\RateInsurance;
use Illuminate\Support\Carbon;

/**
 * Shipping rate calculation engine (pure PHP, no external APIs).
 */
class RateCalculator
{
    /** Volumetric divisor: cm³ / divisor → kg */
    public const VOLUMETRIC_DIVISOR = 5000;

    /**
     * Chargeable weight = max(actual, volumetric).
     */
    public function volumetricWeight(float $lengthCm, float $widthCm, float $heightCm, int $divisor = null): float
    {
        $divisor = $divisor ?: self::VOLUMETRIC_DIVISOR;

        return round(($lengthCm * $widthCm * $heightCm) / $divisor, 2);
    }

    public function chargeableWeight(float $actualKg, float $lengthCm, float $widthCm, float $heightCm): float
    {
        return max($actualKg, $this->volumetricWeight($lengthCm, $widthCm, $heightCm));
    }

    /**
     * Full itemized quote.
     *
     * @return array{ok: bool, breakdown: array[], total: float, rule?: RateRule, transit?: string, message?: string}
     */
    public function calculate(int $originZoneId, int $destinationZoneId, float $weightKg, string $serviceType = 'standard', array $opts = []): array
    {
        $weight     = max(0, $weightKg);
        $declared   = (float) ($opts['declared_value'] ?? 0);
        $applyFuel  = filter_var($opts['apply_fuel'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $applyIns   = filter_var($opts['apply_insurance'] ?? ($declared > 0), FILTER_VALIDATE_BOOLEAN);

        $today   = Carbon::today()->toDateString();
        $rule    = RateRule::query()
            ->where('origin_zone_id', $originZoneId)
            ->where('destination_zone_id', $destinationZoneId)
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->where('weight_min', '<=', $weight)
            ->where('weight_max', '>=', $weight)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $today);
            })
            ->orderByDesc('priority')
            ->orderBy('base_price')
            ->first();

        if (!$rule) {
            return [
                'ok'        => false,
                'message'   => 'No active rate rule matches this route, service and weight bracket.',
                'breakdown' => [],
                'total'     => 0.0,
            ];
        }

        $base    = (float) $rule->base_price + ((float) $rule->per_kg_price * $weight);
        $breakdown = [
            ['label' => 'Base rate (rule #' . $rule->id . ')', 'amount' => round((float) $rule->base_price, 2)],
            ['label' => 'Weight × ' . number_format((float) $rule->per_kg_price, 2) . '/kg (' . $weight . ' kg)', 'amount' => round((float) $rule->per_kg_price * $weight, 2)],
        ];

        /* ---------- Surcharges ---------- */
        $surcharges = RateSurcharge::query()->where('is_active', true)->get();
        $subtotal   = $base;
        foreach ($surcharges as $s) {
            if ($s->applies_to === 'fuel' && !$applyFuel) {
                continue;
            }
            if ($s->applies_to === 'insurance' && !$applyIns) {
                continue;
            }
            $amount = $s->type === 'percentage'
                ? $subtotal * ((float) $s->value / 100)
                : (float) $s->value;
            $breakdown[] = ['label' => $s->name . ($s->type === 'percentage' ? ' (' . rtrim(rtrim((string) $s->value, '0'), '.') . '%)' : ''), 'amount' => round($amount, 2)];
            $subtotal += $amount;
        }

        /* ---------- Insurance ---------- */
        if ($applyIns && $declared > 0) {
            $tier = RateInsurance::query()
                ->where('is_active', true)
                ->where('declared_value_min', '<=', $declared)
                ->where(function ($q) use ($declared) {
                    $q->whereNull('declared_value_max')->orWhere('declared_value_max', '>=', $declared);
                })
                ->orderByDesc('declared_value_min')
                ->first();
            if ($tier) {
                $breakdown[] = ['label' => 'Insurance (declared $' . number_format($declared, 2) . ')', 'amount' => round((float) $tier->cost, 2)];
                $subtotal += (float) $tier->cost;
            }
        }

        $transit = null;
        if ($rule->transit_days_min || $rule->transit_days_max) {
            $transit = trim(($rule->transit_days_min ?? $rule->transit_days_max) . '–' . ($rule->transit_days_max ?? $rule->transit_days_min) . ' days', '–');
        }

        return [
            'ok'        => true,
            'total'     => round($subtotal, 2),
            'breakdown' => $breakdown,
            'rule'      => ['id' => $rule->id, 'priority' => $rule->priority],
            'transit'   => $transit,
        ];
    }
}
