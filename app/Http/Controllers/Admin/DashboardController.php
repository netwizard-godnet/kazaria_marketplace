<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Store;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_stores' => Store::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            
            // Revenus détaillés
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'monthly_revenue' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('payment_status', 'paid')
                ->sum('total'),
            'weekly_revenue' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('payment_status', 'paid')
                ->sum('total'),
            'today_revenue' => Order::whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total'),
            'yesterday_revenue' => Order::whereDate('created_at', now()->subDay())
                ->where('payment_status', 'paid')
                ->sum('total'),
            'last_month_revenue' => Order::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->where('payment_status', 'paid')
                ->sum('total'),
            
            // Statistiques des commandes
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'paid_orders' => Order::where('payment_status', 'paid')->count(),
            'unpaid_orders' => Order::where('payment_status', 'pending')->count(),
            
            // Utilisateurs
            'active_users' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'new_users_last_month' => User::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count(),
            'sellers_count' => User::where('is_seller', true)->count(),
            'verified_users' => User::where('is_verified', true)->count(),
            
            // Produits
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::where('stock', '<', 10)->where('stock', '>', 0)->count(),
            'out_of_stock_products' => Product::where('stock', '<=', 0)->count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            
            // Taux de croissance
            'revenue_growth_rate' => $this->calculateRevenueGrowthRate(),
            'orders_growth_rate' => $this->calculateOrdersGrowthRate(),
            'users_growth_rate' => $this->calculateUsersGrowthRate(),
            'growth_rate' => $this->calculateGrowthRate(),
            
            // Statistiques avancées
            'average_order_value' => Order::where('payment_status', 'paid')->avg('total'),
            'total_items_sold' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_status', 'paid')
                ->sum('order_items.quantity'),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'orders_this_week' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'orders_this_month' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            
            // Statistiques des factures
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'pending_invoices' => Invoice::whereIn('status', ['sent', 'draft'])->count(),
            'overdue_invoices' => Invoice::overdue()->count(),
            'total_invoice_amount' => Invoice::where('status', 'paid')->sum('total'),
            'monthly_invoice_amount' => Invoice::whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->where('status', 'paid')
                ->sum('total'),
        ];

        // Commandes récentes
        $recent_orders = Order::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Produits populaires avec plus de détails
        $popular_products = Product::select('products.*')
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.payment_status = "paid" THEN order_items.quantity ELSE 0 END), 0) as sales_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.payment_status = "paid" THEN order_items.total ELSE 0 END), 0) as revenue')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->groupBy('products.id', 'products.name', 'products.price', 'products.stock', 'products.image', 'products.created_at', 'products.updated_at')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();

        // Top clients (par revenus générés)
        $top_customers = User::select('users.*')
            ->selectRaw('COALESCE(COUNT(CASE WHEN orders.payment_status = "paid" THEN orders.id END), 0) as orders_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.payment_status = "paid" THEN orders.total ELSE 0 END), 0) as total_spent')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->groupBy('users.id', 'users.nom', 'users.prenoms', 'users.email', 'users.created_at', 'users.updated_at')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();

        // Commandes par statut (pour graphique)
        $orders_by_status = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Ventes par jour (7 derniers jours)
        $daily_sales = [];
        $daily_labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $daily_labels[] = $date->translatedFormat('D d/m');
            $daily_sales[] = Order::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('total');
        }

        // Données pour les graphiques
        $salesChart = $this->getSalesChartData();
        $chart_data = [
            'sales' => $salesChart['values'],
            'sales_labels' => json_encode($salesChart['labels'], JSON_UNESCAPED_UNICODE),
            'monthly' => $this->getMonthlyChartData(),
            'active_users' => $this->getActiveUsersChartData(),
            'daily_sales' => implode(',', $daily_sales),
            'daily_labels' => json_encode($daily_labels, JSON_UNESCAPED_UNICODE),
            'orders_by_status' => json_encode($orders_by_status->toArray(), JSON_UNESCAPED_UNICODE),
        ];

        return view('admin.dashboard.index', compact(
            'stats',
            'recent_orders',
            'popular_products',
            'top_customers',
            'chart_data'
        ));
    }

    private function calculateGrowthRate()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return $currentMonth > 0 ? 100 : 0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function calculateRevenueGrowthRate()
    {
        $currentMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'paid')
            ->sum('total');
        
        $lastMonth = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('payment_status', 'paid')
            ->sum('total');
        
        if ($lastMonth == 0) return $currentMonth > 0 ? 100 : 0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function calculateOrdersGrowthRate()
    {
        $currentMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $lastMonth = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        if ($lastMonth == 0) return $currentMonth > 0 ? 100 : 0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function calculateUsersGrowthRate()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        if ($lastMonth == 0) return $currentMonth > 0 ? 100 : 0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function getSalesChartData()
    {
        $sales = [];
        $labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $amount = Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'delivered')
                ->sum('total');
            $sales[] = $amount;
            // Labels en français
            $labels[] = ucfirst(
                $date->translatedFormat('M') // Nécessite locale fr_FR paramétrée sinon fallback anglais
            );
        }
        // On retourne à la fois les valeurs et les labels
        return [
            'values' => implode(',', $sales),
            'labels' => $labels,
        ];
    }

    private function getMonthlyChartData()
    {
        // Nombre de commandes livrées (validées)
        $validated = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'delivered')
            ->count();

        // Nombre de commandes en cours (pending + processing)
        $inProgress = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        // Nombre de commandes annulées
        $cancelled = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'cancelled')
            ->count();

        return implode(',', [
            $validated,
            $inProgress,
            $cancelled,
        ]);
    }

    private function getActiveUsersChartData()
    {
        $users = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('updated_at', $date)->count();
            $users[] = $count;
        }
        
        return implode(',', $users);
    }
}

