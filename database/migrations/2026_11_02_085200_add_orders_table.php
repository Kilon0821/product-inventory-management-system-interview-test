<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 64)->unique()->comment('order number');
            $table->decimal('total_amount', 10, 2)->default(0.00)->comment('total amount');
            $table->string('status', 32)->default('pending')->comment('order status: pending, paid, shipped, completed, cancelled');
            $table->timestamps();
            
            // index
            $table->index('order_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
