<?php

namespace App\Services\Carriers;

use App\Services\Carriers\Contracts\CarrierAdapterInterface;
use Illuminate\Support\Facades\Http;

/**
 * Base adapter: implements the shared mock/unconfigured fallback so every
 * carrier card works offline. Real API calls live in subclasses; credentials
 * are read from the env-key list in config — values NEVER leave this class.
 */
abstract class BaseCarrierAdapter implements CarrierAdapterInterface
{
    abstract protected function envKeys(): array;

    abstract protected function endpoint(): string;

    public function configured(): bool
    {
        foreach ($this->envKeys() as $key) {
            if (blank(config('admin_carriers.' . lcfirst($key))) && blank(env($key))) {
                return false;
            }
        }

        return true;
    }

    public function ping(): array
    {
        if ($this->mockMode()) {
            return ['ok' => true, 'mode' => 'mock', 'message' => 'Mock mode active'];
        }
        try {
            $response = Http::timeout(8)
                ->withHeaders($this->authHeaders())
                ->get($this->endpoint());
            if ($response->status() === 401 || $response->status() === 403) {
                return ['ok' => false, 'mode' => 'live', 'message' => 'Credentials rejected'];
            }

            return ['ok' => $response->successful(), 'mode' => 'live', 'message' => $response->successful() ? 'Connected' : 'HTTP ' . $response->status()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mode' => 'live', 'message' => 'Network error (logged)'];
        }
    }

    public function track(string $number): array
    {
        if ($this->mockMode()) {
            return $this->mockResponse($number);
        }
        try {
            return $this->liveTrack($number);
        } catch (\Throwable $e) {
            \Log::warning('carrier-track-failed', ['carrier' => $this->code(), 'error' => $e->getMessage()]);

            return ['carrier' => $this->name(), 'number' => $number, 'status' => 'unknown', 'events' => [], 'note' => 'Tracking temporarily unavailable'];
        }
    }

    /** Subclasses implement the real gateway call (only reached when configured + mock off). */
    protected function liveTrack(string $number): array
    {
        return $this->mockResponse($number); // safe default until a live implementation is added
    }

    protected function mockMode(): bool
    {
        return config('admin_carriers.mock', true) || !$this->configured();
    }

    protected function mockResponse(string $number): array
    {
        $seed = crc32($number);
        $stages = [
            ['Label created', 'Origin facility'],
            ['Picked up', 'Origin facility'],
            ['In transit', 'Regional hub'],
            ['Customs clearance', 'Destination country'],
            ['Out for delivery', 'Local facility'],
            ['Delivered', 'Destination'],
        ];
        $count = 3 + ($seed % 4); // 3-6 deterministic events
        $events = [];
        foreach (array_slice($stages, 0, $count) as $i => $stage) {
            $events[] = [
                'at' => now()->subDays($count - $i)->setTime(9 + ($seed % 8), ($seed >> 3) % 60)->format('M d, Y H:i'),
                'location' => $stage[1],
                'description' => $stage[0],
            ];
        }

        return [
            'carrier' => $this->name(),
            'number' => $number,
            'status' => end($events)['description'],
            'events' => array_reverse($events),
            'note' => 'Mock data (offline demo)',
        ];
    }

    protected function authHeaders(): array
    {
        $headers = [];
        foreach ($this->envKeys() as $key) {
            $value = env($key);
            if (!blank($value)) {
                $headers['X-' . str_replace('_', '-', $key)] = 'configured';
            }
        }

        return $headers;
    }
}
