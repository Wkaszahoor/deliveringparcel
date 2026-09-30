<?php

namespace App\Console\Commands;

use App\Models\Contactus;
use App\Services\ContactClassifier;
use Illuminate\Console\Command;

/**
 * Agent D — backfill contact messages with a category.
 *
 * Fills category + classified_at on every contactuses row where
 * category IS NULL, chunked per config('admin_contacts.classification_chunk').
 */
class ClassifyContacts extends Command
{
    protected $signature = 'contacts:classify';

    protected $description = 'Backfill category + classified_at on contact messages where category is NULL';

    public function handle(ContactClassifier $classifier)
    {
        $chunk = (int) config('admin_contacts.classification_chunk', 500);
        $updated = 0;

        // classified_at is the source of truth: the category column carries a DB
        // default ('general'), so unclassified rows are the ones with no stamp.
        $totalPending = Contactus::whereNull('classified_at')->count();

        if ($totalPending === 0) {
            $this->info('No contact messages need classification (everything already has classified_at).');

            return 0;
        }

        $this->info("Classifying {$totalPending} contact message(s)...");

        Contactus::whereNull('classified_at')
            ->orderBy('id')
            ->chunkById($chunk, function ($contacts) use ($classifier, &$updated) {
                foreach ($contacts as $contact) {
                    $contact->category = $classifier->classify((string) $contact->detail);
                    $contact->classified_at = now();
                    $contact->save();
                    $updated++;
                }
            });

        $this->info("Done. {$updated} contact message(s) classified.");

        return 0;
    }
}
