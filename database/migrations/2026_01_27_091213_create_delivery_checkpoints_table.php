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
        Schema::create('delivery_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');

            // Sewuence
            $table->integer('sequence')->default(0);

            // Type
            $table->enum('type', ['pickup', 'delivery']);

            // Status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped']);

            // Timing
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('load_start_at')->nullable();
            $table->timestamp('load_end_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->integer('load_duration_minutes')->nullable();

            // GPS at Checkpoint
            $table->decimal('arrival_latitude', 10, 7)->nullable();
            $table->decimal('arrival_longitude', 10, 7)->nullable();

            // Photos
            $table->json('photos')->nullable();

            // Recipient Info
            $table->string('recipient_name')->nullable();
            $table->string('recipient_signature_path')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['delivery_id', 'sequence']);
            $table->index('status');
            $table->unique(['delivery_id', 'location_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_checkpoints');
    }
};
