<?php

namespace App\Http\Controllers;

use App\Models\ClusteringAnalysis;
use App\Models\ClusteringResult;
use App\Models\Product;
use App\Services\ExportService;
use App\Services\KMeansService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClusteringController extends Controller
{
    protected KMeansService $kMeansService;
    protected ExportService $exportService;

    public function __construct(KMeansService $kMeansService, ExportService $exportService)
    {
        $this->kMeansService = $kMeansService;
        $this->exportService = $exportService;
    }

    /**
     * Main Clustering Execution Studio.
     */
    public function index(Request $request)
    {
        // Default to last 12 months or full available range
        $startDate = $request->get('start_date', '2025-01-01');
        $endDate = $request->get('end_date', '2026-04-30');
        $kValue = (int) $request->get('k_value', 3);
        $maxIterations = (int) $request->get('max_iterations', 100);
        $initMethod = $request->get('init_method', 'kmeans_plus');

        // Extract dataset for period
        $dataset = $this->kMeansService->extractFeatures($startDate, $endDate);

        $clusteringOutput = null;
        $hasRun = $request->has('run') || $request->isMethod('post');

        if ($hasRun && count($dataset) > 0) {
            $clusteringOutput = $this->kMeansService->runClustering(
                $dataset,
                $kValue,
                $maxIterations,
                ['total_qty', 'frequency', 'total_revenue', 'raw_lemon_kg'],
                $initMethod
            );
        }

        return view('clustering.index', compact(
            'startDate',
            'endDate',
            'kValue',
            'maxIterations',
            'initMethod',
            'dataset',
            'clusteringOutput',
            'hasRun'
        ));
    }

    /**
     * Save Clustering Result to Database.
     */
    public function save(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'k_value' => ['required', 'integer', 'min:2', 'max:5'],
            'notes' => ['nullable', 'string'],
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $kValue = (int) $request->input('k_value');
        $maxIterations = (int) $request->input('max_iterations', 100);
        $initMethod = $request->input('init_method', 'kmeans_plus');

        $dataset = $this->kMeansService->extractFeatures($startDate, $endDate);
        $output = $this->kMeansService->runClustering($dataset, $kValue, $maxIterations, ['total_qty', 'frequency', 'total_revenue', 'raw_lemon_kg'], $initMethod);

        DB::beginTransaction();
        try {
            $analysis = ClusteringAnalysis::create([
                'title' => $request->input('title'),
                'period_start' => $startDate,
                'period_end' => $endDate,
                'k_value' => $kValue,
                'max_iterations' => $maxIterations,
                'iterations_count' => $output['iterations_count'],
                'is_converged' => $output['converged'],
                'sse_inertia' => $output['sse_inertia'],
                'davies_bouldin_index' => $output['davies_bouldin_index'],
                'features' => $output['features'],
                'initial_centroids' => $output['initial_centroids'],
                'final_centroids' => $output['final_centroids'],
                'cluster_summary' => $output['cluster_summary'],
                'raw_data_snapshot' => $output['raw_data'],
                'iteration_history' => $output['iteration_history'],
                'notes' => $request->input('notes'),
                'created_by' => Auth::id(),
            ]);

            foreach ($output['results'] as $res) {
                ClusteringResult::create([
                    'clustering_analysis_id' => $analysis->id,
                    'product_id' => $res['product_id'],
                    'product_name' => $res['product_name'],
                    'product_code' => $res['product_code'],
                    'category_name' => $res['category_name'],
                    'total_qty' => $res['total_qty'],
                    'frequency' => $res['frequency'],
                    'total_revenue' => $res['total_revenue'],
                    'raw_lemon_kg' => $res['raw_lemon_kg'],
                    'normalized_vector' => $res['normalized_vector'],
                    'cluster_index' => $res['cluster_index'],
                    'cluster_code' => $res['cluster_code'],
                    'cluster_label' => $res['cluster_label'],
                    'distance_to_centroid' => $res['distance_to_centroid'],
                    'inventory_strategy' => $res['inventory_strategy'],
                ]);
            }

            DB::commit();
            return redirect()->route('clustering.show', $analysis)->with('success', "Analisis clustering '{$analysis->title}' berhasil disimpan ke riwayat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan hasil clustering: ' . $e->getMessage());
        }
    }

    /**
     * View History of All Analyses.
     */
    public function history()
    {
        $analyses = ClusteringAnalysis::with(['user', 'results'])->orderBy('created_at', 'desc')->paginate(10);
        return view('clustering.history', compact('analyses'));
    }

    /**
     * Show single saved clustering analysis.
     */
    public function show(ClusteringAnalysis $clustering)
    {
        $clustering->load(['results.product.category', 'user']);
        return view('clustering.show', compact('clustering'));
    }

    /**
     * Compare Two Historical Analyses (Antarperiode).
     */
    public function compare(Request $request)
    {
        $allAnalyses = ClusteringAnalysis::orderBy('period_start', 'asc')->get();

        $analysisIdA = $request->get('analysis_a');
        $analysisIdB = $request->get('analysis_b');

        // Default to first two if not selected
        if (!$analysisIdA && $allAnalyses->count() > 0) {
            $analysisIdA = $allAnalyses->first()->id;
        }
        if (!$analysisIdB && $allAnalyses->count() > 1) {
            $analysisIdB = $allAnalyses->skip(1)->first()->id;
        }

        $analysisA = null;
        $analysisB = null;
        $comparison = [];

        if ($analysisIdA && $analysisIdB) {
            $analysisA = ClusteringAnalysis::with('results.product')->find($analysisIdA);
            $analysisB = ClusteringAnalysis::with('results.product')->find($analysisIdB);

            if ($analysisA && $analysisB) {
                $resultsA = $analysisA->results->keyBy('product_id');
                $resultsB = $analysisB->results->keyBy('product_id');

                $allProductIds = $resultsA->keys()->merge($resultsB->keys())->unique();

                foreach ($allProductIds as $pId) {
                    $itemA = $resultsA->get($pId);
                    $itemB = $resultsB->get($pId);

                    $prodName = $itemA ? $itemA->product_name : ($itemB ? $itemB->product_name : 'Produk');
                    $prodCode = $itemA ? $itemA->product_code : ($itemB ? $itemB->product_code : '-');

                    $qtyA = $itemA ? $itemA->total_qty : 0;
                    $qtyB = $itemB ? $itemB->total_qty : 0;
                    $qtyDiff = $qtyB - $qtyA;

                    $revA = $itemA ? (float) $itemA->total_revenue : 0;
                    $revB = $itemB ? (float) $itemB->total_revenue : 0;
                    $revDiff = $revB - $revA;

                    $clusterA = $itemA ? $itemA->cluster_code : '-';
                    $clusterB = $itemB ? $itemB->cluster_code : '-';

                    $rankA = $itemA ? $itemA->cluster_index : 99;
                    $rankB = $itemB ? $itemB->cluster_index : 99;

                    // Movement: Naik (1 is best), Turun, Tetap
                    $trend = 'tetap';
                    if ($rankB < $rankA) {
                        $trend = 'naik'; // Improved rank (e.g. from C2 to C1)
                    } elseif ($rankB > $rankA) {
                        $trend = 'turun'; // Decreased rank (e.g. from C1 to C2)
                    }

                    $comparison[] = [
                        'product_id' => $pId,
                        'product_name' => $prodName,
                        'product_code' => $prodCode,
                        'item_a' => $itemA,
                        'item_b' => $itemB,
                        'qty_a' => $qtyA,
                        'qty_b' => $qtyB,
                        'qty_diff' => $qtyDiff,
                        'rev_a' => $revA,
                        'rev_b' => $revB,
                        'rev_diff' => $revDiff,
                        'cluster_a' => $clusterA,
                        'cluster_b' => $clusterB,
                        'trend' => $trend,
                    ];
                }

                // Sort comparison by qty diff desc
                usort($comparison, function ($x, $y) {
                    return $y['qty_diff'] <=> $x['qty_diff'];
                });
            }
        }

        return view('clustering.compare', compact(
            'allAnalyses',
            'analysisIdA',
            'analysisIdB',
            'analysisA',
            'analysisB',
            'comparison'
        ));
    }

    public function destroy(ClusteringAnalysis $clustering)
    {
        $title = $clustering->title;
        $clustering->delete();
        return redirect()->route('clustering.history')->with('success', "Riwayat analisis '{$title}' berhasil dihapus.");
    }

    public function exportPdf(ClusteringAnalysis $clustering)
    {
        return $this->exportService->exportClusteringPdf($clustering);
    }

    public function exportExcel(ClusteringAnalysis $clustering)
    {
        return $this->exportService->exportClusteringExcel($clustering);
    }
}
