<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Afficher la liste des factures
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'order', 'creator'])->latest();

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par client
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtre par date d'émission
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // Recherche par numéro de facture ou nom du client
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(15)->appends($request->except('page'));

        // Statistiques
        $stats = [
            'total' => Invoice::count(),
            'draft' => Invoice::where('status', 'draft')->count(),
            'sent' => Invoice::where('status', 'sent')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::overdue()->count(),
            'total_amount' => Invoice::where('status', 'paid')->sum('total'),
            'pending_amount' => Invoice::whereIn('status', ['sent', 'draft'])->sum('total'),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        // Récupérer tous les utilisateurs clients et vendeurs
        $users = User::where(function($q) {
            $q->where('is_seller', true)
              ->orWhere(function($q2) {
                  $q2->where('is_seller', false)
                     ->where('is_admin', false);
              });
        })->orderBy('nom')->orderBy('prenoms')->get();
        
        $orders = Order::where('status', '!=', 'cancelled')->latest()->get();
        
        // Si un order_id est fourni, pré-remplir les données
        $order = null;
        if ($request->filled('order_id')) {
            $order = Order::with(['user', 'orderItems.product'])->find($request->order_id);
        }

        return view('admin.invoices.create', compact('users', 'orders', 'order'));
    }

    /**
     * Créer une nouvelle facture
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_address' => 'nullable|string',
            'client_city' => 'nullable|string|max:100',
            'client_postal_code' => 'nullable|string|max:20',
            'client_country' => 'nullable|string|max:100',
            'client_tax_id' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_tax_id' => 'nullable|string|max:100',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled,refunded',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'paid_date' => 'nullable|date',
            'payment_method' => 'nullable|in:card,mobile_money,cash,bank_transfer,other',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
        ]);

        // Calculer le sous-total à partir des items si fournis
        if (isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0) {
            $itemsSubtotal = 0;
            foreach ($validated['items'] as $item) {
                if (isset($item['total'])) {
                    $itemsSubtotal += floatval($item['total']);
                } elseif (isset($item['quantity']) && isset($item['price'])) {
                    $itemsSubtotal += floatval($item['quantity']) * floatval($item['price']);
                }
            }
            // Utiliser le sous-total calculé si le champ subtotal n'est pas rempli ou est différent
            if (!isset($validated['subtotal']) || $validated['subtotal'] == 0 || abs($validated['subtotal'] - $itemsSubtotal) > 0.01) {
                $validated['subtotal'] = $itemsSubtotal;
            }
        }

        // Calculer le montant de la TVA si nécessaire
        if (!isset($validated['tax_amount']) && isset($validated['tax_rate']) && isset($validated['subtotal'])) {
            $validated['tax_amount'] = ($validated['subtotal'] * $validated['tax_rate']) / 100;
        }

        // Recalculer le total final
        $subtotal = $validated['subtotal'] ?? 0;
        $taxAmount = $validated['tax_amount'] ?? 0;
        $shipping = $validated['shipping_cost'] ?? 0;
        $discount = $validated['discount'] ?? 0;
        $validated['total'] = $subtotal + $taxAmount + $shipping - $discount;

        // Générer le numéro de facture
        $validated['invoice_number'] = Invoice::generateInvoiceNumber();
        $validated['created_by'] = auth()->id();

        // Le cast 'array' du modèle Invoice va automatiquement encoder en JSON lors de la sauvegarde
        // et décoder automatiquement lors de la récupération
        // On s'assure juste que c'est bien un tableau, pas besoin de json_encode
        if (isset($validated['items']) && !is_array($validated['items'])) {
            // Si ce n'est pas un tableau, essayer de le décoder
            if (is_string($validated['items'])) {
                $validated['items'] = json_decode($validated['items'], true) ?? [];
            } else {
                $validated['items'] = [];
            }
        }

        $invoice = Invoice::create($validated);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Facture créée avec succès.');
    }

    /**
     * Afficher une facture
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'order.orderItems.product', 'creator']);
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Invoice $invoice)
    {
        // Récupérer tous les utilisateurs clients et vendeurs
        $users = User::where(function($q) {
            $q->where('is_seller', true)
              ->orWhere(function($q2) {
                  $q2->where('is_seller', false)
                     ->where('is_admin', false);
              });
        })->orderBy('nom')->orderBy('prenoms')->get();
        
        $orders = Order::where('status', '!=', 'cancelled')->latest()->get();
        $invoice->load(['user', 'order']);
        
        return view('admin.invoices.edit', compact('invoice', 'users', 'orders'));
    }

    /**
     * Mettre à jour une facture
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_address' => 'nullable|string',
            'client_city' => 'nullable|string|max:100',
            'client_postal_code' => 'nullable|string|max:20',
            'client_country' => 'nullable|string|max:100',
            'client_tax_id' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_tax_id' => 'nullable|string|max:100',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled,refunded',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'paid_date' => 'nullable|date',
            'payment_method' => 'nullable|in:card,mobile_money,cash,bank_transfer,other',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
        ]);

        // Calculer le sous-total à partir des items si fournis
        if (isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0) {
            $itemsSubtotal = 0;
            foreach ($validated['items'] as $item) {
                if (isset($item['total'])) {
                    $itemsSubtotal += floatval($item['total']);
                } elseif (isset($item['quantity']) && isset($item['price'])) {
                    $itemsSubtotal += floatval($item['quantity']) * floatval($item['price']);
                }
            }
            // Utiliser le sous-total calculé si le champ subtotal n'est pas rempli ou est différent
            if (!isset($validated['subtotal']) || $validated['subtotal'] == 0 || abs($validated['subtotal'] - $itemsSubtotal) > 0.01) {
                $validated['subtotal'] = $itemsSubtotal;
            }
        }

        // Calculer le montant de la TVA si nécessaire
        if (!isset($validated['tax_amount']) && isset($validated['tax_rate']) && isset($validated['subtotal'])) {
            $validated['tax_amount'] = ($validated['subtotal'] * $validated['tax_rate']) / 100;
        }

        // Recalculer le total final
        $subtotal = $validated['subtotal'] ?? 0;
        $taxAmount = $validated['tax_amount'] ?? 0;
        $shipping = $validated['shipping_cost'] ?? 0;
        $discount = $validated['discount'] ?? 0;
        $validated['total'] = $subtotal + $taxAmount + $shipping - $discount;

        // Le cast 'array' du modèle Invoice va automatiquement encoder en JSON lors de la sauvegarde
        // et décoder automatiquement lors de la récupération
        // On s'assure juste que c'est bien un tableau, pas besoin de json_encode
        if (isset($validated['items']) && !is_array($validated['items'])) {
            // Si ce n'est pas un tableau, essayer de le décoder
            if (is_string($validated['items'])) {
                $validated['items'] = json_decode($validated['items'], true) ?? [];
            } else {
                $validated['items'] = [];
            }
        }

        $invoice->update($validated);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Facture mise à jour avec succès.');
    }

    /**
     * Supprimer une facture
     */
    public function destroy(Invoice $invoice)
    {
        // Ne pas permettre la suppression si la facture est payée
        if ($invoice->status === 'paid') {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une facture payée. Veuillez d\'abord changer le statut de la facture.');
        }

        // Supprimer le fichier PDF associé s'il existe
        if ($invoice->pdf_path && File::exists(storage_path('app/public/' . $invoice->pdf_path))) {
            File::delete(storage_path('app/public/' . $invoice->pdf_path));
        }

        $invoice->delete();

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Générer un PDF de facture
     */
    public function download(Invoice $invoice)
    {
        $invoice->load(['user', 'order', 'creator']);
        
        // S'assurer que les items sont bien décodés en tableau
        // Le cast 'array' devrait le faire automatiquement, mais on vérifie quand même
        if (is_string($invoice->items)) {
            $invoice->items = json_decode($invoice->items, true) ?? [];
        }
        if (!is_array($invoice->items)) {
            $invoice->items = [];
        }
        
        // Récupérer les paramètres de l'entreprise depuis les settings
        $companyName = $invoice->company_name ?? Setting::get('site_name', 'KAZARIA');
        $companyEmail = $invoice->company_email ?? Setting::get('site_email', 'contact@kazaria.ci');
        $companyPhone = $invoice->company_phone ?? Setting::get('site_phone', '+225 XX XX XX XX XX');
        $companyAddress = $invoice->company_address ?? Setting::get('site_address', 'Côte d\'Ivoire');
        
        // Générer le PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.pdf', [
            'invoice' => $invoice,
            'companyName' => $companyName,
            'companyEmail' => $companyEmail,
            'companyPhone' => $companyPhone,
            'companyAddress' => $companyAddress,
        ]);
        
        $fileName = 'facture-' . $invoice->invoice_number . '.pdf';
        
        // Sauvegarder le PDF si pas encore sauvegardé
        if (!$invoice->pdf_path) {
            $pdfPath = storage_path('app/public/invoices/');
            if (!file_exists($pdfPath)) {
                mkdir($pdfPath, 0777, true);
            }
            $pdfFullPath = $pdfPath . $fileName;
            $pdf->save($pdfFullPath);
            $invoice->update(['pdf_path' => 'invoices/' . $fileName]);
        }
        
        return $pdf->download($fileName);
    }

    /**
     * Rechercher des produits pour l'autocomplétion
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'price', 'stock', 'image')
            ->limit(20)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'display' => $product->name . ' - ' . number_format($product->price, 0, ',', ' ') . ' FCFA'
                ];
            });

        return response()->json($products);
    }

    /**
     * Récupérer les items d'une commande
     */
    public function getOrderItems(Order $order)
    {
        $order->load('orderItems');
        
        $items = $order->orderItems->map(function($item) {
            return [
                'description' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->total,
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $items,
            'subtotal' => $order->subtotal,
            'total' => $order->total,
        ]);
    }
}
