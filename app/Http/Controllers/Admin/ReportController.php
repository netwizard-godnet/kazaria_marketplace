<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Statistiques pour la page d'accueil des rapports
        $stats = [
            'total_orders' => Order::count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'monthly_revenue' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('payment_status', 'paid')
                ->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'low_stock_products' => Product::where('stock', '<', 10)->count(),
            // Statistiques des factures
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_invoice_amount' => Invoice::where('status', 'paid')->sum('total'),
            'monthly_invoice_amount' => Invoice::whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->where('status', 'paid')
                ->sum('total'),
            'overdue_invoices' => Invoice::overdue()->count(),
        ];

        return view('admin.reports.index', compact('stats'));
    }

    public function sales(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $status = $request->input('status'); // Nouveau filtre par statut

        // Requête principale pour les données de vente
        $query = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_amount'),
                DB::raw('AVG(total) as avg_amount')
            );

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        } else {
            // Par défaut, dernier mois
            $query->whereDate('created_at', '>=', now()->subMonth()->startOfDay());
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($categoryId) {
            $query->whereHas('orderItems.product', function($q) use ($categoryId){
                $q->where('category_id', (int)$categoryId);
            });
        }
        if ($subcategoryId) {
            $query->whereHas('orderItems.product', function($q) use ($subcategoryId){
                $q->where('subcategory_id', (int)$subcategoryId);
            });
        }

        $salesData = $query->groupBy('date')->orderBy('date')->get();

        // Statistiques détaillées par statut
        $statsByStatus = Order::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        // Top produits vendus
        $topProducts = \App\Models\OrderItem::select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('orders.created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('orders.created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // Top catégories
        $topCategories = \App\Models\Category::select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('orders.created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('orders.created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->where('orders.payment_status', 'paid')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        $categories = \App\Models\Category::active()->ordered()->with('subcategories')->get();
        $categoriesJson = $categories->map(function($c){
            return [
                'id' => $c->id,
                'name' => $c->name,
                'subcategories' => $c->subcategories->map(function($s){
                    return ['id' => $s->id, 'name' => $s->name];
                })->values(),
            ];
        })->values();

        // Totaux et statistiques avancées
        $baseTotalQuery = Order::query();
        if ($dateFrom) {
            $baseTotalQuery->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $baseTotalQuery->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }

        $totals = [
            'orders' => (int) $baseTotalQuery->count(),
            'paid_orders' => (int) (clone $baseTotalQuery)->where('payment_status', 'paid')->count(),
            'amount' => (float) (clone $baseTotalQuery)->where('payment_status', 'paid')->sum('total'),
            'avg_order_value' => (float) (clone $baseTotalQuery)->where('payment_status', 'paid')->avg('total'),
            'total_items' => (int) \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->when($dateFrom, function($q) use ($dateFrom) {
                    $q->whereDate('orders.created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
                })
                ->when($dateTo, function($q) use ($dateTo) {
                    $q->whereDate('orders.created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
                })
                ->where('orders.payment_status', 'paid')
                ->sum('order_items.quantity'),
        ];

        // Données pour graphiques
        $chartData = [
            'labels' => $salesData->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            })->toArray(),
            'amounts' => $salesData->pluck('total_amount')->toArray(),
            'orders' => $salesData->pluck('orders_count')->toArray(),
        ];

        return view('admin.reports.sales', compact(
            'salesData',
            'categories',
            'totals',
            'categoriesJson',
            'topProducts',
            'topCategories',
            'statsByStatus',
            'chartData',
            'status'
        ));
    }

    public function users(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $query = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as users_count')
            );
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }

        $usersData = $query->groupBy('date')->orderBy('date')->get();

        // Statistiques détaillées
        $baseQuery = User::query();
        if ($dateFrom) {
            $baseQuery->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $baseQuery->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }

        $totals = [
            'users' => (int) $usersData->sum('users_count'),
            'total_users' => (int) User::count(),
            'verified_users' => (int) (clone $baseQuery)->where('is_verified', true)->count(),
            'sellers' => (int) (clone $baseQuery)->where('is_seller', true)->count(),
            'new_today' => (int) User::whereDate('created_at', today())->count(),
            'new_this_month' => (int) User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Top utilisateurs par nombre de commandes
        $topUsers = User::select('users.*')
            ->selectRaw('COALESCE(COUNT(orders.id), 0) as orders_count')
            ->selectRaw('COALESCE(SUM(orders.total), 0) as total_spent')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('users.created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('users.created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->groupBy('users.id')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        // Données pour graphique
        $chartData = [
            'labels' => $usersData->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            })->toArray(),
            'values' => $usersData->pluck('users_count')->toArray(),
        ];

        return view('admin.reports.users', compact('usersData', 'totals', 'topUsers', 'chartData'));
    }

    public function products(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $query = Product::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as products_count')
            );
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }

        $productsData = $query->groupBy('date')->orderBy('date')->get();

        // Statistiques détaillées
        $baseQuery = Product::query();
        if ($dateFrom) {
            $baseQuery->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $baseQuery->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }

        $totals = [
            'products' => (int) $productsData->sum('products_count'),
            'total_products' => (int) Product::count(),
            'active_products' => (int) (clone $baseQuery)->where('is_active', true)->count(),
            'featured_products' => (int) (clone $baseQuery)->where('is_featured', true)->count(),
            'low_stock' => (int) Product::where('stock', '<', 10)->where('stock', '>', 0)->count(),
            'out_of_stock' => (int) Product::where('stock', '<=', 0)->count(),
        ];

        // Top produits par vues
        $topViewed = Product::select('products.*')
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('products.created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('products.created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get();

        // Données pour graphique
        $chartData = [
            'labels' => $productsData->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            })->toArray(),
            'values' => $productsData->pluck('products_count')->toArray(),
        ];

        return view('admin.reports.products', compact('productsData', 'totals', 'topViewed', 'chartData'));
    }

    public function invoices(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $status = $request->input('status');

        // Requête principale pour les données de factures
        $query = Invoice::select(
                DB::raw('DATE(invoice_date) as date'),
                DB::raw('COUNT(*) as invoices_count'),
                DB::raw('SUM(total) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as paid_amount'),
                DB::raw('AVG(total) as avg_amount')
            );

        if ($dateFrom) {
            $query->whereDate('invoice_date', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->whereDate('invoice_date', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }
        if ($status) {
            $query->where('status', $status);
        }

        $invoicesData = $query->groupBy('date')->orderBy('date')->get();

        // Statistiques détaillées par statut
        $statsByStatus = Invoice::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('invoice_date', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('invoice_date', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        // Top clients par factures
        $topClients = User::select('users.*')
            ->selectRaw('COALESCE(COUNT(invoices.id), 0) as invoices_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN invoices.status = "paid" THEN invoices.total ELSE 0 END), 0) as total_paid')
            ->selectRaw('COALESCE(SUM(invoices.total), 0) as total_invoiced')
            ->leftJoin('invoices', 'users.id', '=', 'invoices.user_id')
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('invoices.invoice_date', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('invoices.invoice_date', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->groupBy('users.id', 'users.nom', 'users.prenoms', 'users.email', 'users.created_at', 'users.updated_at')
            ->havingRaw('COUNT(invoices.id) > 0')
            ->orderBy('total_paid', 'desc')
            ->limit(10)
            ->get();

        // Factures en retard
        $overdueInvoices = Invoice::overdue()
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->whereDate('invoice_date', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->whereDate('invoice_date', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            })
            ->with('user')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // Totaux et statistiques avancées
        $baseTotalQuery = Invoice::query();
        if ($dateFrom) {
            $baseTotalQuery->whereDate('invoice_date', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $baseTotalQuery->whereDate('invoice_date', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }

        $totals = [
            'invoices' => (int) $baseTotalQuery->count(),
            'paid_invoices' => (int) (clone $baseTotalQuery)->where('status', 'paid')->count(),
            'pending_invoices' => (int) (clone $baseTotalQuery)->whereIn('status', ['sent', 'draft'])->count(),
            'overdue_invoices' => (int) (clone $baseTotalQuery)->overdue()->count(),
            'total_amount' => (float) (clone $baseTotalQuery)->sum('total'),
            'paid_amount' => (float) (clone $baseTotalQuery)->where('status', 'paid')->sum('total'),
            'unpaid_amount' => (float) (clone $baseTotalQuery)->where('status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum('total'),
            'avg_invoice_amount' => (float) (clone $baseTotalQuery)->avg('total'),
        ];

        // Données pour graphiques
        $chartData = [
            'labels' => $invoicesData->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            })->toArray(),
            'amounts' => $invoicesData->pluck('total_amount')->toArray(),
            'paid_amounts' => $invoicesData->pluck('paid_amount')->toArray(),
            'counts' => $invoicesData->pluck('invoices_count')->toArray(),
        ];

        return view('admin.reports.invoices', compact(
            'invoicesData',
            'totals',
            'topClients',
            'overdueInvoices',
            'statsByStatus',
            'chartData',
            'status'
        ));
    }

    public function export($type)
    {
        $filename = 'rapport_' . $type . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($type) {
            $handle = fopen('php://output', 'w');
            if ($type === 'sales') {
                fputcsv($handle, ['Date', 'Nb commandes', 'Montant']);
                $rows = Order::select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as orders_count'),
                        DB::raw('SUM(total) as total_amount')
                    )
                    ->groupBy('date')->orderBy('date')->get();
                foreach ($rows as $r) { fputcsv($handle, [$r->date, $r->orders_count, number_format($r->total_amount, 2, '.', '')]); }
            } elseif ($type === 'users') {
                fputcsv($handle, ['Date', 'Nb utilisateurs']);
                $rows = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as users_count'))
                    ->groupBy('date')->orderBy('date')->get();
                foreach ($rows as $r) { fputcsv($handle, [$r->date, $r->users_count]); }
            } elseif ($type === 'invoices') {
                fputcsv($handle, ['Date', 'Nb factures', 'Montant total', 'Montant payé']);
                $rows = Invoice::select(
                        DB::raw('DATE(invoice_date) as date'),
                        DB::raw('COUNT(*) as invoices_count'),
                        DB::raw('SUM(total) as total_amount'),
                        DB::raw('SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as paid_amount')
                    )
                    ->groupBy('date')->orderBy('date')->get();
                foreach ($rows as $r) { 
                    fputcsv($handle, [
                        $r->date, 
                        $r->invoices_count, 
                        number_format($r->total_amount, 2, '.', ''),
                        number_format($r->paid_amount, 2, '.', '')
                    ]); 
                }
            } else { // products
                fputcsv($handle, ['Date', 'Nb produits']);
                $rows = Product::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as products_count'))
                    ->groupBy('date')->orderBy('date')->get();
                foreach ($rows as $r) { fputcsv($handle, [$r->date, $r->products_count]); }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

