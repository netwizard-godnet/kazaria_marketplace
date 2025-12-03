<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductView;

class CleanupProductViews extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'product-views:cleanup {--days=30 : Number of days to keep views}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old product views to keep the database optimized';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = ProductView::where('created_at', '<', $cutoffDate)->delete();
        
        $this->info("Deleted {$deletedCount} product views older than {$days} days.");
        
        return Command::SUCCESS;
    }
}
