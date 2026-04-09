<?php

namespace Kholil\Nitik\Commands;

use Illuminate\Console\Command;
use Kholil\Nitik\Models\NitikError;
use Carbon\Carbon;

class PruneCommand extends Command
{
    protected $signature = 'nitik:prune {--days=30}';
    protected $description = 'Prune old error records';

    public function handle()
    {
        $days = (int) $this->option('days');
        $date = Carbon::now()->subDays($days);

        $count = NitikError::where('last_seen_at', '<', $date)->delete();
        $this->info("Successfully pruned {$count} error records older than {$days} days.");
    }
}
