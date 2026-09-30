<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Cron-friendly queue drain for shared hosting (no Supervisor/Horizon).
 * cPanel cron, every minute:
 *   /opt/alt/php81/usr/bin/php /home/<user>/public_html/deliveringparcel/artisan dp:queue-drain
 * Runs the worker only until the queues are empty, max ~55s, then exits —
 * overlapping crons are harmless because the database queue locks jobs.
 */
class QueueDrain extends Command
{
    protected $signature = 'dp:queue-drain';

    protected $description = 'Process queued jobs (emails queue first, then default) until empty — for cron on shared hosting';

    public function handle()
    {
        $this->info('[' . now()->toDateTimeString() . '] draining queues: emails,default …');

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--queue'           => 'emails,default',
            '--max-time'        => 55,
            '--tries'           => 3,
            '--sleep'           => 1,
        ]);

        $this->info(Artisan::output());
        $this->info('[' . now()->toDateTimeString() . '] drain finished.');

        return 0;
    }
}
