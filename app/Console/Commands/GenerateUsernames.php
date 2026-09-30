<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UsernameService;
use Illuminate\Support\Facades\DB;

class GenerateUsernames extends Command
{
    protected $signature   = 'usernames:generate {--force : Regenerate all}';
    protected $description = 'Generate CUS-XXXXXX usernames for all existing users';

    public function handle(UsernameService $svc): int
    {
        $query = DB::table('users');
        if (!$this->option('force')) {
            $query->whereNull('customer_username');
        }
        $users = $query->get();
        $bar   = $this->output->createProgressBar($users->count());
        $bar->start();
        $count = 0;
        foreach ($users as $user) {
            $svc->ensureCustomerUsername((int) $user->id);
            $bar->advance();
            $count++;
        }
        $bar->finish();
        $this->newLine();
        $this->info("Generated {$count} customer usernames.");

        return 0;
    }
}
