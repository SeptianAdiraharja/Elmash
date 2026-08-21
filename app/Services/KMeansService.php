<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesTransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KMeansService
{
    /**
     * Preprocess sales data for clustering within date range.
     */
    public function extractFeatures(string $startDate, string $endDate): array
    {
        $products = Product::with('category')->where('is_active', true)->get();

        // Get aggregated metrics for each product in date range
        $aggregated = DB::table('sales_transaction_items')
            ->join('sales_transactions', 'sales_transactions.id', '=', 'sales_transaction_items.sales_transaction_id')
            ->whereBetween('sales_transactions.transaction_date', [$startDate, $endDate])
            ->where('sales_transactions.payment_status', '!=', 'Dibatalkan')
            ->select(
                'sales_transaction_items.product_id',
                DB::raw('SUM(sales_transaction_items.quantity) as total_qty'),
                DB::raw('COUNT(DISTINCT sales_transactions.id) as frequency'),
                DB::raw('SUM(sales_transaction_items.subtotal) as total_revenue'),
                DB::raw('SUM(sales_transaction_items.raw_lemon_used) as raw_lemon_kg')
            )
            ->groupBy('sales_transaction_items.product_id')
            ->get()
            ->keyBy('product_id');

        $dataset = [];
        foreach ($products as $p) {
            $metric = $aggregated->get($p->id);
            $qty = $metric ? (int) $metric->total_qty : 0;
            $freq = $metric ? (int) $metric->frequency : 0;
            $rev = $metric ? (float) $metric->total_revenue : 0;
            $rawLemon = $metric ? (float) $metric->raw_lemon_kg : 0;

            // If rawLemon is 0 but qty > 0, compute based on requirement
            if ($rawLemon <= 0 && $qty > 0) {
                $rawLemon = $qty * (float) $p->raw_lemon_requirement;
            }

            $dataset[] = [
                'product_id' => $p->id,
                'product_code' => $p->code,
                'product_name' => $p->name,
                'category_name' => $p->category ? $p->category->name : 'Umum',
                'unit' => $p->unit,
                'selling_price' => (float) $p->selling_price,
                'cost_price' => (float) $p->cost_price,
                'raw_lemon_requirement' => (float) $p->raw_lemon_requirement,
                'features' => [
                    'total_qty' => $qty,
                    'frequency' => $freq,
                    'total_revenue' => $rev,
                    'raw_lemon_kg' => round($rawLemon, 3),
                ]
            ];
        }

        return $dataset;
    }

    /**
     * Run K-Means Clustering Algorithm.
     */
    public function runClustering(
        array $dataset,
        int $k = 3,
        int $maxIterations = 100,
        array $selectedFeatures = ['total_qty', 'frequency', 'total_revenue', 'raw_lemon_kg'],
        string $initMethod = 'kmeans_plus'
    ): array {
        $n = count($dataset);
        if ($n === 0) {
            throw new \Exception('Data produk tidak ditemukan untuk diproses.');
        }

        if ($k > $n) {
            $k = max(1, $n);
        }

        // 1. Min-Max Normalization
        $minMax = [];
        foreach ($selectedFeatures as $f) {
            $values = array_column(array_column($dataset, 'features'), $f);
            $minMax[$f] = [
                'min' => count($values) ? min($values) : 0,
                'max' => count($values) ? max($values) : 1,
            ];
        }

        $normalizedData = [];
        foreach ($dataset as $idx => $row) {
            $normVector = [];
            foreach ($selectedFeatures as $f) {
                $min = $minMax[$f]['min'];
                $max = $minMax[$f]['max'];
                $val = $row['features'][$f];
                if ($max - $min == 0) {
                    $normVector[$f] = 0.0;
                } else {
                    $normVector[$f] = round(($val - $min) / ($max - $min), 5);
                }
            }
            $normalizedData[$idx] = $normVector;
        }

        // 2. Centroid Initialization
        $centroids = $this->initializeCentroids($normalizedData, $k, $selectedFeatures, $initMethod);
        $initialCentroids = $centroids;

        $iterationHistory = [];
        $converged = false;
        $iteration = 0;
        $clusters = array_fill(0, $k, []);
        $distances = [];

        while ($iteration < $maxIterations && !$converged) {
            $iteration++;
            $clusters = array_fill(0, $k, []);
            $distances = [];

            // Step A: Calculate Euclidean distance & Assign to nearest centroid
            foreach ($normalizedData as $i => $vector) {
                $minDist = INF;
                $assignedCluster = 0;
                $distRow = [];

                for ($c = 0; $c < $k; $c++) {
                    $dist = $this->euclideanDistance($vector, $centroids[$c], $selectedFeatures);
                    $distRow[$c] = round($dist, 5);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $assignedCluster = $c;
                    }
                }

                $clusters[$assignedCluster][] = $i;
                $distances[$i] = [
                    'distances_to_centroids' => $distRow,
                    'assigned_cluster' => $assignedCluster,
                    'min_distance' => round($minDist, 5),
                ];
            }

            // Step B: Recalculate centroids
            $newCentroids = [];
            for ($c = 0; $c < $k; $c++) {
                if (empty($clusters[$c])) {
                    // Re-seed empty cluster with centroid
                    $newCentroids[$c] = $centroids[$c];
                    continue;
                }

                $meanVector = [];
                foreach ($selectedFeatures as $f) {
                    $sum = 0;
                    foreach ($clusters[$c] as $itemIdx) {
                        $sum += $normalizedData[$itemIdx][$f];
                    }
                    $meanVector[$f] = round($sum / count($clusters[$c]), 5);
                }
                $newCentroids[$c] = $meanVector;
            }

            // Record iteration state
            $iterationHistory[] = [
                'iteration' => $iteration,
                'centroids_before' => $centroids,
                'centroids_after' => $newCentroids,
                'cluster_counts' => array_map('count', $clusters),
            ];

            // Step C: Check convergence (centroid movement < 0.0001)
            $totalShift = 0;
            for ($c = 0; $c < $k; $c++) {
                $totalShift += $this->euclideanDistance($centroids[$c], $newCentroids[$c], $selectedFeatures);
            }

            if ($totalShift < 0.00001) {
                $converged = true;
            }

            $centroids = $newCentroids;
        }

        // 3. Compute SSE (Sum of Squared Errors) / Inertia
        $sse = 0.0;
        foreach ($clusters as $c => $members) {
            foreach ($members as $mIdx) {
                $dist = $this->euclideanDistance($normalizedData[$mIdx], $centroids[$c], $selectedFeatures);
                $sse += ($dist * $dist);
            }
        }

        // 4. Rank Clusters by Sales Performance (Total Qty & Revenue)
        $clusterMetrics = [];
        for ($c = 0; $c < $k; $c++) {
            $memberCount = count($clusters[$c]);
            $sumQty = 0;
            $sumRev = 0;
            $sumFreq = 0;
            $sumRaw = 0;

            foreach ($clusters[$c] as $mIdx) {
                $sumQty += $dataset[$mIdx]['features']['total_qty'];
                $sumRev += $dataset[$mIdx]['features']['total_revenue'];
                $sumFreq += $dataset[$mIdx]['features']['frequency'];
                $sumRaw += $dataset[$mIdx]['features']['raw_lemon_kg'];
            }

            $avgQty = $memberCount > 0 ? $sumQty / $memberCount : 0;
            $avgRev = $memberCount > 0 ? $sumRev / $memberCount : 0;

            $clusterMetrics[$c] = [
                'raw_cluster_id' => $c,
                'member_count' => $memberCount,
                'total_qty' => $sumQty,
                'avg_qty' => round($avgQty, 2),
                'total_revenue' => $sumRev,
                'avg_revenue' => round($avgRev, 2),
                'total_frequency' => $sumFreq,
                'total_raw_lemon_kg' => round($sumRaw, 2),
                'centroid_normalized' => $centroids[$c],
            ];
        }

        // Sort descending by avg_qty + avg_revenue to give standard labels:
        // Rank 1 -> Tinggi (Laris), Rank 2 -> Sedang, Rank 3 -> Rendah
        $rankedClusterIds = array_keys($clusterMetrics);
        usort($rankedClusterIds, function ($a, $b) use ($clusterMetrics) {
            // Sort by average quantity then revenue
            if ($clusterMetrics[$a]['avg_qty'] == $clusterMetrics[$b]['avg_qty']) {
                return $clusterMetrics[$b]['avg_revenue'] <=> $clusterMetrics[$a]['avg_revenue'];
            }
            return $clusterMetrics[$b]['avg_qty'] <=> $clusterMetrics[$a]['avg_qty'];
        });

        // Define labels based on rank
        $labelMap = [];
        $colorMap = [];
        $strategyMap = [];
        $rankLabels = [
            1 => [
                'code' => 'C1',
                'label' => 'Penjualan Tinggi (Sangat Laris)',
                'badge' => 'emerald',
                'description' => 'Produk unggulan dengan permintaan pasar dan perputaran sangat tinggi.',
                'strategy' => 'Prioritaskan ketersediaan bahan baku lemon segar utama (buffer stock 20%). Jadwalkan produksi harian berkesinambungan untuk mencegah stockout/kehabisan barang.',
            ],
            2 => [
                'code' => 'C2',
                'label' => 'Penjualan Sedang (Cukup Diminati)',
                'badge' => 'amber',
                'description' => 'Produk dengan penjualan stabil dan peminat berkala.',
                'strategy' => 'Terapkan produksi semi-batch mingguan (make-to-stock terukur). Alokasikan bahan baku lemon segar sesuai estimasi PO mingguan agar efisien.',
            ],
            3 => [
                'code' => 'C3',
                'label' => 'Penjualan Rendah (Kurang Diminati)',
                'badge' => 'rose',
                'description' => 'Produk slow-moving dengan volume dan frekuensi penjualan rendah.',
                'strategy' => 'Batasi pengadaan lemon segar untuk produk ini guna mencegah overstock & pembusukan. Buat paket bundling promo dengan produk C1 atau kaji ulang formulasi/kemasan.',
            ],
            4 => [
                'code' => 'C4',
                'label' => 'Penjualan Sangat Rendah',
                'badge' => 'slate',
                'description' => 'Produk dengan pergerakan minimal.',
                'strategy' => 'Sistem Make-To-Order (hanya diproduksi jika ada PO khusus). Minimalisir penyimpanan lemon segar untuk varian ini.',
            ],
            5 => [
                'code' => 'C5',
                'label' => 'Penjualan Khusus / Ekstra Rendah',
                'badge' => 'purple',
                'description' => 'Produk niche/musiman.',
                'strategy' => 'Evaluasi kelayakan kontinuitas produk atau jual musiman.',
            ],
        ];

        $clusterSummary = [];
        $mappingOldToRank = [];

        foreach ($rankedClusterIds as $rankIdx => $oldClusterId) {
            $rankNum = $rankIdx + 1;
            $meta = $rankLabels[$rankNum] ?? [
                'code' => 'C' . $rankNum,
                'label' => 'Klaster ' . $rankNum,
                'badge' => 'slate',
                'description' => 'Kategori klaster ke-' . $rankNum,
                'strategy' => 'Lakukan monitoring berkala atas pergerakan stok.',
            ];

            $mappingOldToRank[$oldClusterId] = [
                'rank_number' => $rankNum,
                'cluster_code' => $meta['code'],
                'cluster_label' => $meta['label'],
                'badge' => $meta['badge'],
                'description' => $meta['description'],
                'strategy' => $meta['strategy'],
            ];

            $summaryEntry = $clusterMetrics[$oldClusterId];
            $summaryEntry['rank'] = $rankNum;
            $summaryEntry['cluster_code'] = $meta['code'];
            $summaryEntry['cluster_label'] = $meta['label'];
            $summaryEntry['badge'] = $meta['badge'];
            $summaryEntry['description'] = $meta['description'];
            $summaryEntry['strategy'] = $meta['strategy'];
            $clusterSummary[$meta['code']] = $summaryEntry;
        }

        // 5. Calculate Davies-Bouldin Index (DBI)
        $dbi = $this->calculateDaviesBouldinIndex($normalizedData, $clusters, $centroids, $selectedFeatures);

        // 6. Build Final Results per Product
        $finalResults = [];
        foreach ($dataset as $idx => $row) {
            $assignedOldCluster = $distances[$idx]['assigned_cluster'];
            $clusterInfo = $mappingOldToRank[$assignedOldCluster];

            $finalResults[] = [
                'product_id' => $row['product_id'],
                'product_code' => $row['product_code'],
                'product_name' => $row['product_name'],
                'category_name' => $row['category_name'],
                'unit' => $row['unit'],
                'selling_price' => $row['selling_price'],
                'cost_price' => $row['cost_price'],
                'total_qty' => $row['features']['total_qty'],
                'frequency' => $row['features']['frequency'],
                'total_revenue' => $row['features']['total_revenue'],
                'raw_lemon_kg' => $row['features']['raw_lemon_kg'],
                'normalized_vector' => $normalizedData[$idx],
                'cluster_index' => $clusterInfo['rank_number'],
                'cluster_code' => $clusterInfo['cluster_code'],
                'cluster_label' => $clusterInfo['cluster_label'],
                'badge' => $clusterInfo['badge'],
                'distance_to_centroid' => $distances[$idx]['min_distance'],
                'distances_all' => $distances[$idx]['distances_to_centroids'],
                'inventory_strategy' => $clusterInfo['strategy'],
            ];
        }

        // Sort final results by total_qty descending
        usort($finalResults, function ($a, $b) {
            if ($a['cluster_index'] == $b['cluster_index']) {
                return $b['total_qty'] <=> $a['total_qty'];
            }
            return $a['cluster_index'] <=> $b['cluster_index'];
        });

        return [
            'k' => $k,
            'max_iterations' => $maxIterations,
            'iterations_count' => $iteration,
            'converged' => $converged,
            'sse_inertia' => round($sse, 5),
            'davies_bouldin_index' => round($dbi, 4),
            'features' => $selectedFeatures,
            'min_max' => $minMax,
            'initial_centroids' => $initialCentroids,
            'final_centroids' => $centroids,
            'cluster_summary' => $clusterSummary,
            'results' => $finalResults,
            'raw_data' => $dataset,
            'normalized_data' => $normalizedData,
            'iteration_history' => $iterationHistory,
        ];
    }

    /**
     * Initialize centroids using K-Means++ or Spread.
     */
    private function initializeCentroids(array $data, int $k, array $features, string $method = 'kmeans_plus'): array
    {
        $n = count($data);
        if ($n <= $k) {
            $centroids = [];
            for ($i = 0; $i < $k; $i++) {
                $centroids[] = $data[$i % $n];
            }
            return $centroids;
        }

        if ($method === 'spread') {
            // Pick evenly spaced indices
            $centroids = [];
            $step = max(1, floor($n / $k));
            for ($i = 0; $i < $k; $i++) {
                $idx = min($n - 1, (int) ($i * $step));
                $centroids[] = $data[$idx];
            }
            return $centroids;
        }

        // K-Means++ Initialization
        $centroids = [];
        // First centroid: pick first product or middle
        $firstIdx = 0;
        $centroids[] = $data[$firstIdx];

        for ($c = 1; $c < $k; $c++) {
            $distancesSq = [];
            $sumDistSq = 0.0;

            for ($i = 0; $i < $n; $i++) {
                $minD = INF;
                foreach ($centroids as $cent) {
                    $d = $this->euclideanDistance($data[$i], $cent, $features);
                    if ($d < $minD) {
                        $minD = $d;
                    }
                }
                $dSq = $minD * $minD;
                $distancesSq[$i] = $dSq;
                $sumDistSq += $dSq;
            }

            // Pick next centroid with probability proportional to D(x)^2
            if ($sumDistSq > 0) {
                // Find point with highest D(x)^2 for deterministic optimal seed
                $maxIdx = 0;
                $maxVal = -1;
                foreach ($distancesSq as $idx => $val) {
                    if ($val > $maxVal) {
                        $maxVal = $val;
                        $maxIdx = $idx;
                    }
                }
                $centroids[] = $data[$maxIdx];
            } else {
                $centroids[] = $data[($c * 3) % $n];
            }
        }

        return $centroids;
    }

    /**
     * Compute Euclidean Distance between two feature vectors.
     */
    public function euclideanDistance(array $v1, array $v2, array $features): float
    {
        $sum = 0.0;
        foreach ($features as $f) {
            $val1 = $v1[$f] ?? 0.0;
            $val2 = $v2[$f] ?? 0.0;
            $diff = $val1 - $val2;
            $sum += ($diff * $diff);
        }
        return sqrt($sum);
    }

    /**
     * Calculate Davies-Bouldin Index (DBI) for cluster validation.
     */
    private function calculateDaviesBouldinIndex(array $data, array $clusters, array $centroids, array $features): float
    {
        $k = count($clusters);
        if ($k <= 1) return 0.0;

        // 1. Average distance of all points in cluster i to centroid i (Si)
        $s = [];
        for ($i = 0; $i < $k; $i++) {
            $count = count($clusters[$i]);
            if ($count === 0) {
                $s[$i] = 0.0;
                continue;
            }
            $sumDist = 0.0;
            foreach ($clusters[$i] as $itemIdx) {
                $sumDist += $this->euclideanDistance($data[$itemIdx], $centroids[$i], $features);
            }
            $s[$i] = $sumDist / $count;
        }

        // 2. For each cluster i, find max (Si + Sj) / d(Ci, Cj)
        $r = [];
        for ($i = 0; $i < $k; $i++) {
            $maxR = 0.0;
            for ($j = 0; $j < $k; $j++) {
                if ($i === $j) continue;
                $dCentroid = $this->euclideanDistance($centroids[$i], $centroids[$j], $features);
                if ($dCentroid > 0.00001) {
                    $val = ($s[$i] + $s[$j]) / $dCentroid;
                    if ($val > $maxR) {
                        $maxR = $val;
                    }
                }
            }
            $r[$i] = $maxR;
        }

        // 3. DBI = (1/k) * sum(Ri)
        return array_sum($r) / $k;
    }
}
