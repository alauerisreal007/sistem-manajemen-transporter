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
        Schema::create('delivery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('checkpoint_id')->constrained('delivery_checkpoints')->onDelete('cascade');

            // Photo Info
            $table->string('photo_path');
            $table->enum('photo_type', ['pickup', 'delivery', 'proof', 'damage']);

            // GPS when photo taken
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Metadata
            $table->timestamp('captured_at');
            $table->string('caption')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['delivery_id', 'checkpoint_id']);
            $table->index('photo_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_photos');
    }
};
