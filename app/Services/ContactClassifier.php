<?php

namespace App\Services;

/**
 * Agent D — keyword-based contact message classifier.
 *
 * Categories + keywords come from config/admin_contacts.php.
 * Categories are evaluated in ascending `priority` order (lower wins),
 * and the first category with any keyword hit wins. When nothing
 * matches, the configured default category ('other') is returned.
 */
class ContactClassifier
{
    /**
     * Classify a raw contact message into a category key.
     */
    public function classify(string $message): string
    {
        $haystack = mb_strtolower(trim($message));

        if ($haystack === '') {
            return $this->defaultCategory();
        }

        $categories = collect(config('admin_contacts.categories', []))
            ->sortBy(fn ($cat, $key) => $cat['priority'] ?? 99);

        foreach ($categories as $key => $cat) {
            foreach ((array) ($cat['keywords'] ?? []) as $keyword) {
                $keyword = mb_strtolower(trim((string) $keyword));

                if ($keyword !== '' && str_contains($haystack, $keyword)) {
                    return (string) $key;
                }
            }
        }

        return $this->defaultCategory();
    }

    protected function defaultCategory(): string
    {
        return (string) config('admin_contacts.default_category', 'other');
    }
}
