<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Numéro de facture unique (ex: FACT-2025-001)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Client
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null'); // Commande associée (optionnelle)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Admin qui a créé la facture
            
            // Informations du client (peuvent être différentes de celles de l'utilisateur)
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone')->nullable();
            $table->text('client_address')->nullable();
            $table->string('client_city')->nullable();
            $table->string('client_postal_code')->nullable();
            $table->string('client_country')->default('CI');
            $table->string('client_tax_id')->nullable(); // Numéro d'identification fiscale
            
            // Informations de l'entreprise (émettrice)
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_tax_id')->nullable();
            
            // Montants
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // Taux de TVA en pourcentage
            $table->decimal('tax_amount', 10, 2)->default(0); // Montant de la TVA
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Statut et dates
            $table->enum('status', [
                'draft',        // Brouillon
                'sent',         // Envoyée
                'paid',         // Payée
                'overdue',      // En retard
                'cancelled',    // Annulée
                'refunded'      // Remboursée
            ])->default('draft');
            
            $table->date('invoice_date'); // Date d'émission
            $table->date('due_date')->nullable(); // Date d'échéance
            $table->date('paid_date')->nullable(); // Date de paiement
            
            // Informations de paiement
            $table->enum('payment_method', ['card', 'mobile_money', 'cash', 'bank_transfer', 'other'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            
            // Notes et informations supplémentaires
            $table->text('notes')->nullable(); // Notes internes
            $table->text('terms')->nullable(); // Conditions générales
            $table->text('description')->nullable(); // Description des services/produits
            
            // Fichier PDF
            $table->string('pdf_path')->nullable(); // Chemin vers le PDF généré
            
            // Métadonnées
            $table->text('items')->nullable(); // JSON pour stocker les lignes de facture
            
            $table->timestamps();
            
            // Index pour recherches rapides
            $table->index('invoice_number');
            $table->index('user_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('invoice_date');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
