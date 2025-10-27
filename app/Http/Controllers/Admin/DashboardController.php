<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Store;
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
            'monthly_revenue' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'completed')
                ->sum('total'),
            'active_users' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'growth_rate' => $this->calculateGrowthRate(),
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
        $chart_data = [
            'sales' => $this->getSalesChartData(),
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
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $amount = Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'completed')
                ->sum('total');
            $sales[] = $amount;
        }
        
        return implode(',', $sales);
    }

    private function getMonthlyChartData()
    {
        $completed = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('total');
            
        $cancelled = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'cancelled')
            ->sum('total');
            
        $total = $completed + $cancelled;
        
        if ($total == 0) return '100,0';
        
        $completedPercent = round(($completed / $total) * 100);
        $cancelledPercent = 100 - $completedPercent;
        
        return $completedPercent . ',' . $cancelledPercent;
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

