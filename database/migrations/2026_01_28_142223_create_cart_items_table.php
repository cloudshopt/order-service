<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();

            $table->unsignedBigInteger('product_id');
            $table->string('name_snapshot', 180);
            $table->unsignedInteger('unit_price_snapshot'); // isto kot product.price
            $table->unsignedInteger('qty');

            $table->timestamps();

            $table->unique(['cart_id', 'product_id']); // 1 product = 1 item (qty se povečuje)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};