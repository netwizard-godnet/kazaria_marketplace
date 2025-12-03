<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
use App\Models\Review;

class RecalculateStoreRatingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:recalculate-ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule les notes de tous les vendeurs basées sur les notes de leurs produits';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Recalcul des notes des vendeurs...');
        $this->newLine();

        $stores = Store::all();
        $total = $stores->count();
        $updated = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($stores as $store) {
            try {
                // Utiliser la méthode statique pour mettre à jour la note
                Review::updateStoreRating($store->id);
                $updated++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("⚠️  Erreur pour la boutique {$store->name} (ID: {$store->id}): " . $e->getMessage());
                $skipped++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Recalcul terminé !");
        $this->line("   - Boutiques mises à jour : {$updated}");
        if ($skipped > 0) {
            $this->warn("   - Boutiques ignorées : {$skipped}");
        }

        return Command::SUCCESS;
    }
}

