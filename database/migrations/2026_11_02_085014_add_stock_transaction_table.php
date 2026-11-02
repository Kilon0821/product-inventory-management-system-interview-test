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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->comment('product ID');
            $table->integer('quantity')->comment('quantity');
            $table->string('action', 50)->comment('action type: in(inbound)/out(outbound)/adjust(adjustment)');
            $table->timestamp('create_at')->nullable()->comment('created time');
            
            // index
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
