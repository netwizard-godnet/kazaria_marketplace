<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AIController;
use Illuminate\Http\Request;

class TestAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var var
     */
    protected $signature = 'ai:test {--question= : Question spécifique à tester}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester les capacités de l\'IA KAZAR I.A';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Activer temporairement l'IA pour les tests
        config(['kazar_ai.enabled' => true]);

        $controller = new AIController();
        
        // Questions de test variées
        $testQuestions = [
            // Small-talk
            'bonjour',
            'salut',
            'comment ça va ?',
            'qui es-tu ?',
            'je m\'appelle Jean',
            
            // Questions FAQ
            'comment suivre ma commande ?',
            'quels sont les moyens de paiement ?',
            'quels sont les délais de livraison ?',
            'puis-je retourner un produit ?',
            'quelle est la garantie ?',
            'comment vous contacter ?',
            'comment devenir vendeur ?',
            'quels sont les frais de livraison ?',
            
            // Questions sur les produits (si des produits existent)
            'quelles catégories avez-vous ?',
            'quelles sont les promotions en cours ?',
            
            // Variations de questions
            'combien coûte un téléphone ?',
            'je veux un téléphone 128GB',
            'montre-moi des téléphones Samsung',
            'j\'ai 30000 FCFA et je veux un téléphone',
        ];

        // Si une question spécifique est fournie, tester uniquement celle-ci
        if ($this->option('question')) {
            $testQuestions = [$this->option('question')];
        }

        $this->info('🧪 Test de l\'IA KAZAR I.A');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        $successCount = 0;
        $failCount = 0;

        foreach ($testQuestions as $question) {
            $this->line("❓ Question: <fg=cyan>{$question}</>");
            
            try {
                $request = Request::create('/api/ai/query', 'POST', [
                    'message' => $question
                ]);
                
                $response = $controller->query($request);
                $data = json_decode($response->getContent(), true);
                
                if ($data && isset($data['success']) && $data['success']) {
                    $this->line("✅ Réponse: <fg=green>{$data['message']}</>");
                    $this->line("   Intent: <fg=yellow>{$data['intent']}</>");
                    if (!empty($data['items'])) {
                        $this->line("   Produits trouvés: " . count($data['items']));
                    }
                    $successCount++;
                } else {
                    $this->line("❌ Erreur: <fg=red>" . ($data['message'] ?? 'Erreur inconnue') . "</>");
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->line("❌ Exception: <fg=red>{$e->getMessage()}</>");
                $this->line("   Fichier: {$e->getFile()}:{$e->getLine()}");
                $failCount++;
            }
            
            $this->newLine();
        }

        $this->info('═══════════════════════════════════════');
        $this->info("📊 Résultats: <fg=green>{$successCount} succès</> / <fg=red>{$failCount} échecs</>");
        
        if ($failCount > 0) {
            $this->warn('⚠️  Certains tests ont échoué. Vérifiez les erreurs ci-dessus.');
            return Command::FAILURE;
        }
        
        $this->info('✅ Tous les tests sont passés !');
        return Command::SUCCESS;
    }
}
