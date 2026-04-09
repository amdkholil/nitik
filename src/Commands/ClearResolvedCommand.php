<?php

namespace Kholil\Nitik\Commands;

use Illuminate\Console\Command;
use Kholil\Nitik\Models\NitikError;

class ClearResolvedCommand extends Command
{
    protected $signature = 'nitik:clear-resolved';
    protected $description = 'Clear all resolved error records';

    public function handle()
    {
        $count = NitikError::where('is_resolved', true)->delete();
        $this->info("Successfully cleared {$count} resolved error records.");
    }
}
