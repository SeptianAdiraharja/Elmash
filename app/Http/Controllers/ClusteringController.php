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
     * SESUAI SKRIPSI: Default menggunakan metode 'skripsi_manual'
     */
    public function index(Request $request)
    {
        // Default sesuai skripsi: data dari 01/09/2025 s/d 27/04/2026
        $startDate = $request->get('start_date', '2025-09-01');
        $endDate = $request->get('end_date', '2026-04-27');
        $kValue = (int) $request->get('k_value', 3);
        $maxIterations = (int) $request->get('max_iterations', 100);

        // SESUAI SKRIPSI: Default menggunakan 'skripsi_manual'
        $initMethod = $request->get('init_method', 'skripsi_manual');
        $sampleSize = $request->filled('sample_size') ? (int) $request->get('sample_size') : null;

        $dataset = $this->kMeansService->extractFeatures($startDate, $endDate, $sampleSize);

        $clusteringOutput = null;
        $hasRun = $request->has('run') || $request->isMethod('post');

        if ($hasRun && count($dataset) > 0) {
            $clusteringOutput = $this->kMeansService->runClustering(
                $dataset,
                $kValue,
                $maxIterations,
                ['x1_dried_lemon_kg', 'x2_manisan_lemon_pouch', 'x3_sari_lemon_liter'],
                $initMethod
            );
        }

        return view('clustering.index', compact(
            'startDate', 'endDate', 'kValue', 'maxIterations', 'initMethod', 'sampleSize',
            'dataset', 'clusteringOutput', 'hasRun'
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
            'sample_size' => ['nullable', 'integer', 'min:3'],
            'notes' => ['nullable', 'string'],
            'init_method' => ['nullable', 'string', 'in:skripsi_manual,representative,kmeans_plus'],
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $kValue = (int) $request->input('k_value');
        $maxIterations = (int) $request->input('max_iterations', 100);

        // SESUAI SKRIPSI: Default 'skripsi_manual'
        $initMethod = $request->input('init_method', 'skripsi_manual');
        $sampleSize = $request->filled('sample_size') ? (int) $request->input('sample_size') : null;

        $dataset = $this->kMeansService->extractFeatures($startDate, $endDate, $sampleSize);
        $output = $this->kMeansService->runClustering(
            $dataset, $kValue, $maxIterations,
            ['x1_dried_lemon_kg', 'x2_manisan_lemon_pouch', 'x3_sari_lemon_liter'],
            $initMethod
        );

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
                'elbow_data' => $output['elbow_data'], // PERBAIKAN: Simpan elbow data
                'notes' => $request->input('notes'),
                'created_by' => Auth::id(),
            ]);

            foreach ($output['results'] as $res) {
                ClusteringResult::create([
                    'clustering_analysis_id' => $analysis->id,
                    'transaction_date' => $res['transaction_date'],
                    'day_name' => $res['day_name'],
                    'x1_dried_lemon_kg' => $res['x1_dried_lemon_kg'],
                    'x2_manisan_lemon_pouch' => $res['x2_manisan_lemon_pouch'],
                    'x3_sari_lemon_liter' => $res['x3_sari_lemon_liter'],
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
        $clustering->load(['results', 'user']);

        // Siapkan elbow data untuk ditampilkan di view
        $elbowData = null;

        // PERBAIKAN: Cek elbow_data dari database terlebih dahulu
        if ($clustering->elbow_data) {
            $elbowData = $clustering->elbow_data;
        } elseif ($clustering->raw_data_snapshot) {
            try {
                $dataset = $clustering->raw_data_snapshot;
                $elbowData = $this->kMeansService->computeElbowMethod(
                    $dataset,
                    10,
                    ['x1_dried_lemon_kg', 'x2_manisan_lemon_pouch', 'x3_sari_lemon_liter'],
                    'skripsi_manual'
                );
            } catch (\Exception $e) {
                $elbowData = null;
            }
        }

        return view('clustering.show', compact('clustering', 'elbowData'));
    }

    /**
     * Compare Two Historical Analyses (Antarperiode).
     */
    public function compare(Request $request)
    {
        $allAnalyses = ClusteringAnalysis::orderBy('period_start', 'asc')->get();

        $analysisIdA = $request->get('analysis_a');
        $analysisIdB = $request->get('analysis_b');

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
            $analysisA = ClusteringAnalysis::with('results')->find($analysisIdA);
            $analysisB = ClusteringAnalysis::with('results')->find($analysisIdB);

            if ($analysisA && $analysisB) {
                $resultsA = $analysisA->results->keyBy(fn ($r) => $r->transaction_date->toDateString());
                $resultsB = $analysisB->results->keyBy(fn ($r) => $r->transaction_date->toDateString());

                $allDates = $resultsA->keys()->merge($resultsB->keys())->unique()->sort();

                foreach ($allDates as $date) {
                    $itemA = $resultsA->get($date);
                    $itemB = $resultsB->get($date);

                    $clusterA = $itemA ? $itemA->cluster_code : '-';
                    $clusterB = $itemB ? $itemB->cluster_code : '-';
                    $rankA = $itemA ? $itemA->cluster_index : 99;
                    $rankB = $itemB ? $itemB->cluster_index : 99;

                    $trend = 'tetap';
                    if ($rankB < $rankA) {
                        $trend = 'naik';
                    } elseif ($rankB > $rankA) {
                        $trend = 'turun';
                    }

                    $comparison[] = [
                        'transaction_date' => $date,
                        'day_name' => $itemA ? $itemA->day_name : ($itemB ? $itemB->day_name : '-'),
                        'cluster_a' => $clusterA,
                        'cluster_b' => $clusterB,
                        'trend' => $trend,
                        'x1_a' => $itemA->x1_dried_lemon_kg ?? 0,
                        'x1_b' => $itemB->x1_dried_lemon_kg ?? 0,
                        'x2_a' => $itemA->x2_manisan_lemon_pouch ?? 0,
                        'x2_b' => $itemB->x2_manisan_lemon_pouch ?? 0,
                        'x3_a' => $itemA->x3_sari_lemon_liter ?? 0,
                        'x3_b' => $itemB->x3_sari_lemon_liter ?? 0,
                    ];
                }

                usort($comparison, function ($x, $y) {
                    return strcmp($x['transaction_date'], $y['transaction_date']);
                });
            }
        }

        return view('clustering.compare', compact(
            'allAnalyses', 'analysisIdA', 'analysisIdB', 'analysisA', 'analysisB', 'comparison'
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