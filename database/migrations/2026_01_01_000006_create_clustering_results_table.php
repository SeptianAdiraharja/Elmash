<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clustering_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clustering_analysis_id')->constrained('clustering_analyses')->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('day_name'); // mis. "Kamis, 02-01-2025"
            $table->integer('x1_dried_lemon_kg')->default(0);
            $table->integer('x2_manisan_lemon_pouch')->default(0);
            $table->integer('x3_sari_lemon_liter')->default(0);
            $table->json('normalized_vector')->nullable();
            $table->integer('cluster_index')->default(1);
            $table->string('cluster_code', 10)->default('C1');
            $table->string('cluster_label');
            $table->double('distance_to_centroid')->nullable();
            $table->text('inventory_strategy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clustering_results');
    }
};