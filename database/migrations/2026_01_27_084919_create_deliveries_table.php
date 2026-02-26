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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_code')->unique();

            // Realtions
            $table->foreignId('route_id')->constrained('routes')->onDelete('restrict');
            $table->foreignId('driver_id')->constrained('users')->onDelete('restrict');

            // Status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');

            // Live Tracking
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_duration_minutes')->nullable();

            // Current Progress
            $table->foreignId('current_location_id')->constrained('locations')->nullable();
            $table->integer('current_sequence')->default(0);

            // GPS Tracking
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamp('last_location_update')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->nullable();
            $table->foreignId('updated_by')->constrained('users')->nullable();
            $table->timestamps();

            $table->softDeletes();

            // Indexes
            $table->index('delivery_code');
            $table->index(['driver_id', 'status']);
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
