<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageVisit;
use App\Models\ProductView;
use App\Models\AIInteraction;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index()
    {
        // Statistiques générales de visites
        $stats = [
            'total_visits' => PageVisit::count(),
            'total_visits_today' => PageVisit::whereDate('created_at', today())->count(),
            'total_visits_this_week' => PageVisit::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_visits_this_month' => PageVisit::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'total_clicks' => PageVisit::sum('click_count'),
            'total_clicks_today' => PageVisit::whereDate('created_at', today())->sum('click_count'),
            'unique_visitors_today' => PageVisit::whereDate('created_at', today())->distinct('ip_address')->count('ip_address'),
            'unique_visitors_this_week' => PageVisit::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->distinct('ip_address')->count('ip_address'),
            'unique_visitors_this_month' => PageVisit::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->distinct('ip_address')->count('ip_address'),
            'total_product_views' => ProductView::count(),
            'total_ai_interactions' => AIInteraction::count(),
        ];

        // Pages les plus visitées
        $top_pages = PageVisit::select('page_path', 'page_name', DB::raw('COUNT(*) as visit_count'))
            ->groupBy('page_path', 'page_name')
            ->orderBy('visit_count', 'desc')
            ->limit(10)
            ->get();

        // Pages avec le plus de clics
        $top_clicked_pages = PageVisit::select('page_path', 'page_name', DB::raw('SUM(click_count) as total_clicks'))
            ->groupBy('page_path', 'page_name')
            ->orderBy('total_clicks', 'desc')
            ->limit(10)
            ->get();

        // Visites par jour (30 derniers jours)
        $visits_by_day = PageVisit::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Visites par heure (24 dernières heures)
        $visits_by_hour = PageVisit::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Produits les plus consultés
        $top_products = Product::select('id', 'name', 'views_count')
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get();

        // Interactions AI
        $ai_interactions = AIInteraction::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        // Statistiques de croissance
        $growth = [
            'visits_today_vs_yesterday' => $this->calculateGrowth('visits', 'day'),
            'visits_this_week_vs_last_week' => $this->calculateGrowth('visits', 'week'),
            'visits_this_month_vs_last_month' => $this->calculateGrowth('visits', 'month'),
            'clicks_today_vs_yesterday' => $this->calculateGrowth('clicks', 'day'),
        ];

        // Données pour les graphiques
        $chart_data = [
            'visits_by_day_labels' => $visits_by_day->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d/m'))->toArray(),
            'visits_by_day_values' => $visits_by_day->pluck('count')->toArray(),
            'visits_by_hour_labels' => $visits_by_hour->pluck('hour')->map(fn($hour) => $hour . 'h')->toArray(),
            'visits_by_hour_values' => $visits_by_hour->pluck('count')->toArray(),
        ];

        return view('admin.statistics.index', compact(
            'stats',
            'top_pages',
            'top_clicked_pages',
            'visits_by_day',
            'visits_by_hour',
            'top_products',
            'ai_interactions',
            'growth',
            'chart_data'
        ));
    }

    private function calculateGrowth($type, $period)
    {
        $current = 0;
        $previous = 0;

        if ($type === 'visits') {
            if ($period === 'day') {
                $current = PageVisit::whereDate('created_at', today())->count();
                $previous = PageVisit::whereDate('created_at', today()->subDay())->count();
            } elseif ($period === 'week') {
                $current = PageVisit::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
                $previous = PageVisit::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();
            } elseif ($period === 'month') {
                $current = PageVisit::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
                $previous = PageVisit::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
            }
        } elseif ($type === 'clicks') {
            if ($period === 'day') {
                $current = PageVisit::whereDate('created_at', today())->sum('click_count');
                $previous = PageVisit::whereDate('created_at', today()->subDay())->sum('click_count');
            }
        }

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function getPageStats(Request $request)
    {
        $pagePath = $request->get('page_path');
        
        if (!$pagePath) {
            return response()->json(['error' => 'Page path required'], 400);
        }

        $stats = [
            'total_visits' => PageVisit::where('page_path', $pagePath)->count(),
            'total_clicks' => PageVisit::where('page_path', $pagePath)->sum('click_count'),
            'unique_visitors' => PageVisit::where('page_path', $pagePath)->distinct('ip_address')->count('ip_address'),
            'visits_today' => PageVisit::where('page_path', $pagePath)->whereDate('created_at', today())->count(),
            'clicks_today' => PageVisit::where('page_path', $pagePath)->whereDate('created_at', today())->sum('click_count'),
        ];

        return response()->json($stats);
    }
}

