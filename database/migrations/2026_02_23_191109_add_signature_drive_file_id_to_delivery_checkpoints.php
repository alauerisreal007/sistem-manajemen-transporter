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
        Schema::table('delivery_checkpoints', function (Blueprint $table) {
            $table->string('signature_drive_file_id')->nullable()->after('recipient_signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_checkpoints', function (Blueprint $table) {
            $table->string('signature_drive_file_id')->nullable()->after('recipient_signature_path');
        });
    }
};
