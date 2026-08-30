<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clustering_results', function (Blueprint $table) {
            $table->date('transaction_date')->after('clustering_analysis_id');
            $table->string('day_name', 20)->after('transaction_date');

            $table->integer('x1_dried_lemon_kg')->after('day_name');
            $table->integer('x2_manisan_lemon_pouch')->after('x1_dried_lemon_kg');
            $table->integer('x3_sari_lemon_liter')->after('x2_manisan_lemon_pouch');

            $table->json('normalized_vector')->after('x3_sari_lemon_liter');

            $table->integer('cluster_index')->after('normalized_vector');
            $table->string('cluster_code', 10)->after('cluster_index');
            $table->string('cluster_label')->after('cluster_code');

            $table->decimal('distance_to_centroid', 10, 5)
                ->after('cluster_label');

            $table->text('inventory_strategy')
                ->after('distance_to_centroid');
        });
    }

    public function down(): void
    {
        Schema::table('clustering_results', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_date',
                'day_name',
                'x1_dried_lemon_kg',
                'x2_manisan_lemon_pouch',
                'x3_sari_lemon_liter',
                'normalized_vector',
                'cluster_index',
                'cluster_code',
                'cluster_label',
                'distance_to_centroid',
                'inventory_strategy',
            ]);
        });
    }
};