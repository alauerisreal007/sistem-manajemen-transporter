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
        Schema::create('delivery_gps_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('restrict');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed', 5, 2)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->string('heading')->nullable();

            // Battery Info
            $table->integer('battery_level')->nullable();

            $table->timestamp('recorded_at');

            $table->timestamps();

            $table->index(['delivery_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_gps_trackings');
    }
};
