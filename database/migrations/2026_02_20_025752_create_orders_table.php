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

            $table->string('order_reference_number')->unique();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('vendor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('restaurant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_payment', 10, 2);

            $table->enum('order_status', [
                'PENDING',
                'ACCEPTED',
                'PREPARING',
                'READY',
                'PICKED_UP',
                'IN_TRANSIT',
                'DELIVERED',
                'CANCELLED',
            ])->default('PENDING');

            $table->timestamps();
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
