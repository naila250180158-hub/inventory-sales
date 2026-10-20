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
            // restrictOnDelete: kategori yang masih dipakai produk tidak bisa dihapus
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            // unique di database = lapisan perlindungan kedua setelah validasi Laravel
            $table->string('sku', 50)->unique();
            $table->string('name', 150)->index();
            $table->decimal('purchase_price', 14, 2);
            $table->decimal('selling_price', 14, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('minimum_stock')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
