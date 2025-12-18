<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'order_id',
        'created_by',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'client_city',
        'client_postal_code',
        'client_country',
        'client_tax_id',
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'company_tax_id',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount',
        'shipping_cost',
        'total',
        'status',
        'invoice_date',
        'due_date',
        'paid_date',
        'payment_method',
        'payment_reference',
        'payment_notes',
        'notes',
        'terms',
        'description',
        'pdf_path',
        'items',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'items' => 'array',
    ];

    /**
     * Relation avec l'utilisateur (client)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la commande associée
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relation avec l'admin qui a créé la facture
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Générer un numéro de facture unique
     */
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice && preg_match('/FACT-' . $year . '-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'FACT-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Brouillon',
            'sent' => 'Envoyée',
            'paid' => 'Payée',
            'overdue' => 'En retard',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Obtenir la classe CSS du statut
     */
    public function getStatusClassAttribute()
    {
        $classes = [
            'draft' => 'secondary',
            'sent' => 'info',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark',
            'refunded' => 'warning',
        ];
        
        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * Vérifier si la facture est en retard
     */
    public function isOverdue()
    {
        return $this->status !== 'paid' 
            && $this->status !== 'cancelled' 
            && $this->due_date 
            && $this->due_date->isPast();
    }

    /**
     * Scope pour les factures payées
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope pour les factures en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', now());
    }

    /**
     * Scope pour trier par date récente
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope pour une période donnée
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }
}
