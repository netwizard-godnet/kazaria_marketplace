<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Statistiques pour la page d'accueil des rapports
        $stats = [
            'total_orders' => Order::count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total'),
            'monthly_revenue' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'delivered')
                ->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'low_stock_products' => Product::where('stock', '<', 10)->count(),
        ];

        return view('admin.reports.index', compact('stats'));
    }

    public function sales(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');

        $query = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_amount')
            );

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
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

        // Totaux
        $totals = [
            'orders' => (int) $salesData->sum('orders_count'),
            'amount' => (float) $salesData->sum('total_amount'),
        ];

        return view('admin.reports.sales', compact('salesData', 'categories', 'totals', 'categoriesJson'));
    }

    public function users(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

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

        $totals = [ 'users' => (int) $usersData->sum('users_count') ];

        return view('admin.reports.users', compact('usersData', 'totals'));
    }

    public function products(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

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

        $totals = [ 'products' => (int) $productsData->sum('products_count') ];

        return view('admin.reports.products', compact('productsData', 'totals'));
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

