<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FAQ;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Comment suivre ma commande ?',
                'answer' => 'Vous pouvez suivre votre commande en utilisant votre numéro de commande sur la page de suivi. Le numéro de commande vous a été envoyé par email après validation de votre commande.',
                'keywords' => ['suivre', 'commande', 'suivi', 'tracking', 'livraison', 'statut', 'suivre commande', 'suivi commande'],
                'category' => 'commande',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'Quels sont les moyens de paiement acceptés ?',
                'answer' => 'Nous acceptons plusieurs moyens de paiement : Mobile Money (Orange Money, MTN Mobile Money), paiement à la livraison (selon la zone), et carte bancaire si disponible.',
                'keywords' => ['paiement', 'payer', 'moyen', 'mobile money', 'orange money', 'mtn', 'carte bancaire', 'livraison'],
                'category' => 'paiement',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'Quels sont les délais de livraison ?',
                'answer' => 'Les délais de livraison varient selon votre zone. En général, la livraison se fait sous 24 à 72 heures après validation de la commande. Pour les zones éloignées, cela peut prendre jusqu\'à 5 jours ouvrés.',
                'keywords' => ['livraison', 'délai', 'délais', 'expédition', 'temps', 'combien de temps', 'quand', 'arrivée'],
                'category' => 'livraison',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'question' => 'Puis-je retourner un produit ?',
                'answer' => 'Oui, vous pouvez retourner un produit sous certaines conditions : le produit doit être intact, dans son emballage d\'origine, et la demande de retour doit être faite dans les délais légaux. Contactez notre service client pour initier un retour.',
                'keywords' => ['retour', 'retourner', 'remboursement', 'échanger', 'échange', 'récupérer', 'rendre'],
                'category' => 'retour',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'question' => 'Quelle est la garantie sur les produits ?',
                'answer' => 'La garantie varie selon le produit et le fabricant. En général, les produits électroniques bénéficient d\'une garantie constructeur de 6 à 24 mois. Conservez votre facture et le bon de livraison pour faire valoir la garantie.',
                'keywords' => ['garantie', 'warranty', 'réparation', 'défaut', 'panne', 'casse'],
                'category' => 'garantie',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'question' => 'Comment contacter le service client ?',
                'answer' => 'Vous pouvez nous contacter par téléphone, WhatsApp, email ou via le formulaire de contact sur le site. Nos coordonnées sont disponibles dans la section "Contact" du site.',
                'keywords' => ['contact', 'téléphone', 'whatsapp', 'email', 'appeler', 'appelle', 'numéro', 'coordonnées', 'service client'],
                'category' => 'contact',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'question' => 'Comment devenir vendeur sur KAZARIA ?',
                'answer' => 'Pour devenir vendeur, rendez-vous sur la page "Devenir vendeur" et remplissez le formulaire d\'inscription. Vous devrez fournir certains documents (DFE, registre de commerce) et attendre la validation de votre dossier.',
                'keywords' => ['vendeur', 'devenir vendeur', 'vendre', 'boutique', 'marchand', 'inscription vendeur'],
                'category' => 'vendeur',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'question' => 'Quels sont les frais de livraison ?',
                'answer' => 'Les frais de livraison varient selon la zone et le poids du colis. Les frais sont calculés automatiquement lors de la commande. Certaines zones bénéficient de la livraison gratuite pour les commandes supérieures à un montant minimum.',
                'keywords' => ['frais', 'frais de livraison', 'coût livraison', 'prix livraison', 'tarif livraison', 'livraison gratuite'],
                'category' => 'livraison',
                'order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            FAQ::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }

        $this->command->info('FAQs créées avec succès !');
    }
}
