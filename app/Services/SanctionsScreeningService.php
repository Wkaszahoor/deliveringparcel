<?php

namespace App\Services;

use App\Models\SanctionsEntry;

/**
 * Local-list sanctions screening with fuzzy matching (no external API).
 */
class SanctionsScreeningService
{
    /**
     * @return array{matches: array<int, array{name: string, list: string, country: ?string, score: int}>, count: int}
     */
    public function screen(string $name, ?string $country = null): array
    {
        $threshold = (int) config('admin_compliance.screening_threshold', 70);
        $inputKey  = metaphone(trim($name));
        $inputLen  = strlen($inputKey) ?: 1;
        $matches = [];

        foreach (SanctionsEntry::query()->get() as $entry) {
            $entryKey  = metaphone($entry->full_name);
            $entryLen  = strlen($entryKey) ?: 1;
            $distance  = levenshtein($inputKey, $entryKey);
            $similarity = (int) round(100 - ($distance / max($inputLen, $entryLen)) * 100);

            if ($country && $entry->country && strcasecmp($country, $entry->country) === 0) {
                $similarity += 10; // same country boosts confidence
            }
            if ($similarity >= $threshold) {
                $matches[] = [
                    'name'    => $entry->full_name,
                    'list'    => $entry->list_name,
                    'country' => $entry->country,
                    'score'   => min(100, $similarity),
                ];
            }
        }

        usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);

        return ['matches' => array_slice($matches, 0, 20), 'count' => count($matches)];
    }
}
