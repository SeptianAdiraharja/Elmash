<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('code', 30)->unique();
            $table->string('name', 50);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->string('unit', 20)->default('Botol');
            $table->decimal('raw_lemon_requirement', 8, 3)->default(1.000)->comment('Kebutuhan lemon segar dalam Kg per unit produk');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock_alert')->default(10);
            $table->string('image', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
