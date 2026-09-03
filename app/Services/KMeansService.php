<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KMeansService
{
    /**
     * Ekstrak fitur X1/X2/X3 teragregasi per hari transaksi dalam rentang tanggal.
     */
    public function extractFeatures(string $startDate, string $endDate): array
    {
        $rows = DB::table('sales_transaction_items')
            ->join('sales_transactions', 'sales_transactions.id', '=', 'sales_transaction_items.sales_transaction_id')
            ->join('products', 'products.id', '=', 'sales_transaction_items.product_id')
            ->whereBetween('sales_transactions.transaction_date', [$startDate, $endDate])
            ->where('sales_transactions.payment_status', '!=', 'Dibatalkan')
            ->select(
                'sales_transactions.transaction_date',
                DB::raw("SUM(CASE WHEN products.unit = 'Kg' THEN sales_transaction_items.quantity ELSE 0 END) as x1_dried_lemon_kg"),
                DB::raw("SUM(CASE WHEN products.unit = 'Pouch' THEN sales_transaction_items.quantity ELSE 0 END) as x2_manisan_lemon_pouch"),
                DB::raw("SUM(CASE WHEN products.unit = 'Liter' THEN sales_transaction_items.quantity ELSE 0 END) as x3_sari_lemon_liter")
            )
            ->groupBy('sales_transactions.transaction_date')
            ->orderBy('sales_transactions.transaction_date')
            ->get();

        $dataset = [];
        foreach ($rows as $row) {
            $date = Carbon::parse($row->transaction_date);
            $dataset[] = [
                'transaction_date' => $date->toDateString(),
                'day_name' => $date->translatedFormat('l, d-m-Y'),
                'features' => [
                    'x1_dried_lemon_kg' => (int) $row->x1_dried_lemon_kg,
                    'x2_manisan_lemon_pouch' => (int) $row->x2_manisan_lemon_pouch,
                    'x3_sari_lemon_liter' => (int) $row->x3_sari_lemon_liter,
                ],
            ];
        }

        return $dataset;
    }

    /**
     * Jalankan algoritma K-Means Clustering.
     */
    public function runClustering(
        array $dataset,
        int $k = 3,
        int $maxIterations = 100,
        array $selectedFeatures = ['x1_dried_lemon_kg', 'x2_manisan_lemon_pouch', 'x3_sari_lemon_liter'],
        string $initMethod = 'skripsi_manual' // Disesuaikan dengan skripsi
    ): array {
        $n = count($dataset);
        if ($n === 0) {
            throw new \Exception('Data penjualan harian tidak ditemukan untuk diproses.');
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

            // Step A: Euclidean distance & assign to nearest centroid
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

            $iterationHistory[] = [
                'iteration' => $iteration,
                'centroids_before' => $centroids,
                'centroids_after' => $newCentroids,
                'cluster_counts' => array_map('count', $clusters),
            ];

            // Step C: Check convergence
            $totalShift = 0;
            for ($c = 0; $c < $k; $c++) {
                $totalShift += $this->euclideanDistance($centroids[$c], $newCentroids[$c], $selectedFeatures);
            }

            if ($totalShift < 0.00001) {
                $converged = true;
            }

            $centroids = $newCentroids;
        }

        // 3. SSE / WCSS
        $sse = 0.0;
        foreach ($clusters as $c => $members) {
            foreach ($members as $mIdx) {
                $dist = $this->euclideanDistance($normalizedData[$mIdx], $centroids[$c], $selectedFeatures);
                $sse += ($dist * $dist);
            }
        }

        // 4. Ranking Cluster sesuai Struktur Bab 3 Skripsi:
        //    C1 = Penjualan Rendah (skor terendah)
        //    C2 = Penjualan Sedang (skor menengah)
        //    C3 = Penjualan Tinggi (skor tertinggi)
        $clusterMetrics = [];
        for ($c = 0; $c < $k; $c++) {
            $memberCount = count($clusters[$c]);
            $sumX1 = 0;
            $sumX2 = 0;
            $sumX3 = 0;

            foreach ($clusters[$c] as $mIdx) {
                $sumX1 += $dataset[$mIdx]['features']['x1_dried_lemon_kg'];
                $sumX2 += $dataset[$mIdx]['features']['x2_manisan_lemon_pouch'];
                $sumX3 += $dataset[$mIdx]['features']['x3_sari_lemon_liter'];
            }

            $centroidScore = array_sum($centroids[$c]);

            $clusterMetrics[$c] = [
                'raw_cluster_id' => $c,
                'member_count' => $memberCount,
                'total_x1_dried_lemon_kg' => $sumX1,
                'avg_x1_dried_lemon_kg' => $memberCount > 0 ? round($sumX1 / $memberCount, 2) : 0,
                'total_x2_manisan_lemon_pouch' => $sumX2,
                'avg_x2_manisan_lemon_pouch' => $memberCount > 0 ? round($sumX2 / $memberCount, 2) : 0,
                'total_x3_sari_lemon_liter' => $sumX3,
                'avg_x3_sari_lemon_liter' => $memberCount > 0 ? round($sumX3 / $memberCount, 2) : 0,
                'centroid_score' => round($centroidScore, 5),
                'centroid_normalized' => $centroids[$c],
            ];
        }

        $rankedClusterIds = array_keys($clusterMetrics);
        // Urutkan ascending (dari skor terendah ke tertinggi) untuk menyesuaikan penamaan C1=Rendah, C2=Sedang, C3=Tinggi
        usort($rankedClusterIds, function ($a, $b) use ($clusterMetrics) {
            return $clusterMetrics[$a]['centroid_score'] <=> $clusterMetrics[$b]['centroid_score'];
        });

        // Definisi label klaster yang selaras dengan Bab 3 Skripsi
        $rankLabels = [
            1 => [
                'code' => 'C1',
                'label' => 'Penjualan Rendah',
                'badge' => 'rose',
                'description' => 'Hari dengan volume pengiriman ketiga variabel yang rendah.',
                'strategy' => 'Kurangi pengadaan bahan baku lemon segar pada pola hari seperti ini untuk mencegah overstock/pembusukan.',
            ],
            2 => [
                'code' => 'C2',
                'label' => 'Penjualan Sedang',
                'badge' => 'amber',
                'description' => 'Hari dengan volume penjualan menengah, salah satu variabel bisa menonjol.',
                'strategy' => 'Alokasikan bahan baku lemon segar sesuai estimasi kebutuhan mingguan, produksi semi-batch.',
            ],
            3 => [
                'code' => 'C3',
                'label' => 'Penjualan Tinggi',
                'badge' => 'emerald',
                'description' => 'Hari dengan volume pengiriman Dried Lemon, Manisan Lemon, dan Sari Lemon yang tinggi.',
                'strategy' => 'Tingkatkan pengadaan bahan baku lemon segar (buffer stock) untuk mencegah stockout pada pola hari seperti ini.',
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
                'strategy' => 'Lakukan monitoring berkala atas pola penjualan harian.',
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

        // 5. Davies-Bouldin Index
        $dbi = $this->calculateDaviesBouldinIndex($normalizedData, $clusters, $centroids, $selectedFeatures);

        // 6. Hasil akhir per hari
        $finalResults = [];
        foreach ($dataset as $idx => $row) {
            $assignedOldCluster = $distances[$idx]['assigned_cluster'];
            $clusterInfo = $mappingOldToRank[$assignedOldCluster];

            $finalResults[] = [
                'transaction_date' => $row['transaction_date'],
                'day_name' => $row['day_name'],
                'x1_dried_lemon_kg' => $row['features']['x1_dried_lemon_kg'],
                'x2_manisan_lemon_pouch' => $row['features']['x2_manisan_lemon_pouch'],
                'x3_sari_lemon_liter' => $row['features']['x3_sari_lemon_liter'],
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

        usort($finalResults, function ($a, $b) {
            return strcmp($a['transaction_date'], $b['transaction_date']);
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

    private function initializeCentroids(array $data, int $k, array $features, string $method = 'skripsi_manual'): array
    {
        $n = count($data);
        if ($n <= $k) {
            $centroids = [];
            for ($i = 0; $i < $k; $i++) {
                $centroids[] = $data[$i % $n];
            }
            return $centroids;
        }

        // Metode inisialisasi persis sampel Bab 3 Skripsi (Data ke-3, Data ke-9, Data ke-2)
        if ($method === 'skripsi_manual') {
            $centroids = [];
            $sampleIndices = [2, 8, 1]; // Indeks array basis 0: Data 3 (idx 2), Data 9 (idx 8), Data 2 (idx 1)

            for ($i = 0; $i < $k; $i++) {
                $targetIdx = $sampleIndices[$i] ?? ($i % $n);
                $centroids[] = $data[$targetIdx];
            }
            return $centroids;
        }

        if ($method === 'representative') {
            $scored = [];
            foreach ($data as $idx => $vector) {
                $score = array_sum(array_intersect_key($vector, array_flip($features)));
                $scored[] = ['idx' => $idx, 'score' => $score];
            }

            usort($scored, fn($a, $b) => $a['score'] <=> $b['score']);

            $lowIdx    = $scored[0]['idx'];
            $highIdx   = $scored[$n - 1]['idx'];
            $midIdx    = $scored[intdiv($n, 2)]['idx'];

            $picked = [$lowIdx, $midIdx, $highIdx];

            if ($k > 3) {
                $step = max(1, intdiv($n, $k));
                for ($i = 0; $i < $k; $i++) {
                    $picked[$i] = $scored[min($n - 1, $i * $step)]['idx'];
                }
            }

            $centroids = [];
            for ($i = 0; $i < $k; $i++) {
                $centroids[] = $data[$picked[$i]];
            }
            return $centroids;
        }

        // K-Means++ Default
        $centroids = [];
        $centroids[] = $data[0];

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

            if ($sumDistSq > 0) {
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

    private function calculateDaviesBouldinIndex(array $data, array $clusters, array $centroids, array $features): float
    {
        $k = count($clusters);
        if ($k <= 1) return 0.0;

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

        return array_sum($r) / $k;
    }
}