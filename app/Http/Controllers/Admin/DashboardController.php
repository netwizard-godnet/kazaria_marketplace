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
            // Utiliser le statut réel des commandes livrées pour les revenus
            'monthly_revenue' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'delivered')
                ->sum('total'),
            'active_users' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'growth_rate' => $this->calculateGrowthRate(),
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
            ->limit(5)
            ->get();

        // Produits populaires
        $popular_products = Product::withCount('orderItems as sales_count')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get();

        // Données pour les graphiques
        $salesChart = $this->getSalesChartData();
        $chart_data = [
            'sales' => $salesChart['values'],
            'sales_labels' => json_encode($salesChart['labels'], JSON_UNESCAPED_UNICODE),
            'monthly' => $this->getMonthlyChartData(),
            'active_users' => $this->getActiveUsersChartData(),
        ];

        return view('admin.dashboard.index', compact(
            'stats',
            'recent_orders',
            'popular_products',
            'chart_data'
        ));
    }

    private function calculateGrowthRate()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return 0;
        
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

