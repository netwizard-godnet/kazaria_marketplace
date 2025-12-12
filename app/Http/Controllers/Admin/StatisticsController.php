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
    public function index(Request $request)
    {
        // Récupérer les filtres
        $period = $request->get('period', 'month'); // today, week, month, year, custom
        $pagePath = $request->get('page_path');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Construire la requête de base avec les filtres
        $baseQuery = PageVisit::query();
        
        if ($pagePath) {
            $baseQuery->where('page_path', $pagePath);
        }
        
        // Appliquer le filtre de période
        $dateRange = $this->getDateRange($period, $dateFrom, $dateTo);
        if ($dateRange) {
            $baseQuery->whereBetween('created_at', [$dateRange['from'], $dateRange['to']]);
        }
        
        // Statistiques générales de visites
        $stats = [
            'total_visits' => (clone $baseQuery)->count(),
            'total_clicks' => (clone $baseQuery)->sum('click_count'),
            'unique_visitors' => (clone $baseQuery)->distinct('ip_address')->count('ip_address'),
            'total_product_views' => ProductView::count(),
            'total_ai_interactions' => AIInteraction::count(),
        ];
        
        // Ajouter des statistiques comparatives si nécessaire
        if ($period !== 'custom') {
            $stats['total_visits_today'] = PageVisit::whereDate('created_at', today())->count();
            $stats['total_clicks_today'] = PageVisit::whereDate('created_at', today())->sum('click_count');
            $stats['unique_visitors_today'] = PageVisit::whereDate('created_at', today())->distinct('ip_address')->count('ip_address');
        }

        // Pages les plus visitées
        $topPagesQuery = (clone $baseQuery);
        if ($pagePath) {
            // Si un filtre de page est appliqué, on montre toutes les pages
            $topPagesQuery = PageVisit::query();
            if ($dateRange) {
                $topPagesQuery->whereBetween('created_at', [$dateRange['from'], $dateRange['to']]);
            }
        }
        $top_pages = $topPagesQuery->select('page_path', 'page_name', DB::raw('COUNT(*) as visit_count'))
            ->groupBy('page_path', 'page_name')
            ->orderBy('visit_count', 'desc')
            ->limit(10)
            ->get();

        // Pages avec le plus de clics
        $topClickedQuery = (clone $baseQuery);
        if ($pagePath) {
            $topClickedQuery = PageVisit::query();
            if ($dateRange) {
                $topClickedQuery->whereBetween('created_at', [$dateRange['from'], $dateRange['to']]);
            }
        }
        $top_clicked_pages = $topClickedQuery->select('page_path', 'page_name', DB::raw('SUM(click_count) as total_clicks'))
            ->groupBy('page_path', 'page_name')
            ->orderBy('total_clicks', 'desc')
            ->limit(10)
            ->get();

        // Visites par jour
        $visitsByDayQuery = (clone $baseQuery);
        $visits_by_day = $visitsByDayQuery->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Visites par heure
        $visitsByHourQuery = (clone $baseQuery);
        $visits_by_hour = $visitsByHourQuery->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Produits les plus consultés (pas de filtre de période car basé sur views_count cumulé)
        $top_products = Product::select('id', 'name', 'views_count')
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get();

        // Interactions AI avec filtre de date si applicable
        $aiInteractionsQuery = AIInteraction::query();
        if ($dateRange && !$pagePath) {
            $aiInteractionsQuery->whereBetween('created_at', [$dateRange['from'], $dateRange['to']]);
        }
        $ai_interactions = $aiInteractionsQuery->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        // Statistiques de croissance - seulement si pas de filtre de page spécifique
        $growth = [];
        if (!$pagePath) {
            $growth = [
                'visits_today_vs_yesterday' => $this->calculateGrowth('visits', 'day'),
                'visits_this_week_vs_last_week' => $this->calculateGrowth('visits', 'week'),
                'visits_this_month_vs_last_month' => $this->calculateGrowth('visits', 'month'),
                'clicks_today_vs_yesterday' => $this->calculateGrowth('clicks', 'day'),
            ];
        }

        // Données pour les graphiques
        $chart_data = [
            'visits_by_day_labels' => $visits_by_day->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d/m'))->toArray(),
            'visits_by_day_values' => $visits_by_day->pluck('count')->toArray(),
            'visits_by_hour_labels' => $visits_by_hour->pluck('hour')->map(fn($hour) => $hour . 'h')->toArray(),
            'visits_by_hour_values' => $visits_by_hour->pluck('count')->toArray(),
        ];

        // Liste de toutes les pages pour le filtre
        $all_pages = PageVisit::select('page_path', 'page_name')
            ->distinct()
            ->orderBy('page_name', 'asc')
            ->get()
            ->unique('page_path');

        return view('admin.statistics.index', compact(
            'stats',
            'top_pages',
            'top_clicked_pages',
            'visits_by_day',
            'visits_by_hour',
            'top_products',
            'ai_interactions',
            'growth',
            'chart_data',
            'all_pages',
            'period',
            'pagePath',
            'dateFrom',
            'dateTo'
        ));
    }
    
    private function getDateRange($period, $dateFrom = null, $dateTo = null)
    {
        if ($period === 'custom' && $dateFrom && $dateTo) {
            return [
                'from' => Carbon::parse($dateFrom)->startOfDay(),
                'to' => Carbon::parse($dateTo)->endOfDay(),
            ];
        }
        
        switch ($period) {
            case 'today':
                return [
                    'from' => now()->startOfDay(),
                    'to' => now()->endOfDay(),
                ];
            case 'week':
                return [
                    'from' => now()->startOfWeek(),
                    'to' => now()->endOfWeek(),
                ];
            case 'month':
                return [
                    'from' => now()->startOfMonth(),
                    'to' => now()->endOfMonth(),
                ];
            case 'year':
                return [
                    'from' => now()->startOfYear(),
                    'to' => now()->endOfYear(),
                ];
            default:
                return [
                    'from' => now()->startOfMonth(),
                    'to' => now()->endOfMonth(),
                ];
        }
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

