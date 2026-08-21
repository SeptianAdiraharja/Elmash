<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clustering_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('k_value')->default(3);
            $table->integer('max_iterations')->default(100);
            $table->integer('iterations_count')->default(1);
            $table->boolean('is_converged')->default(true);
            $table->double('sse_inertia')->nullable();
            $table->double('davies_bouldin_index')->nullable();
            $table->json('features')->nullable();
            $table->json('initial_centroids')->nullable();
            $table->json('final_centroids')->nullable();
            $table->json('cluster_summary')->nullable();
            $table->json('raw_data_snapshot')->nullable();
            $table->json('iteration_history')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clustering_analyses');
    }
};
