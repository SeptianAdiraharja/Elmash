<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_lemon_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('period_month', 7)->unique()->comment('Format YYYY-MM');
            $table->string('status')->comment('Kelebihan / Kekurangan / Seimbang');
            $table->decimal('quantity_kg', 10, 2)->default(0);
            $table->decimal('inbound_kg', 10, 2)->default(0);
            $table->decimal('used_in_production_kg', 10, 2)->default(0);
            $table->decimal('waste_kg', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_lemon_stocks');
    }
};
