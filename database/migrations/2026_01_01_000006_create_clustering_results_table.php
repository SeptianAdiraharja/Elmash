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
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_name');
            $table->string('product_code');
            $table->string('category_name')->nullable();
            $table->integer('total_qty')->default(0);
            $table->integer('frequency')->default(0);
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->decimal('raw_lemon_kg', 10, 3)->default(0);
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
