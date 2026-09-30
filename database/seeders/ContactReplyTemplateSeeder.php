<?php

namespace Database\Seeders;

use App\Models\ContactReplyTemplate;
use Illuminate\Database\Seeder;

/**
 * Agent D — seeds the 4 default reply templates from config/admin_contacts.php.
 * Idempotent: keyed on name, so re-running updates the seeded templates in place.
 */
class ContactReplyTemplateSeeder extends Seeder
{
    public function run()
    {
        $count = 0;

        foreach (config('admin_contacts.templates', []) as $template) {
            ContactReplyTemplate::updateOrCreate(
                ['name' => $template['name']],
                [
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->command?->info("ContactReplyTemplateSeeder: {$count} template(s) seeded.");
    }
}
