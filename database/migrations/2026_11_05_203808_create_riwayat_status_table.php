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
        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->restrictOnDelete();
            $table->string('status_lama', 30)->nullable();
            $table->string('status_baru', 30);
            $table->foreignId('diubah_oleh_staff')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('diubah_oleh_pelanggan')->nullable()->constrained('pelanggan')->restrictOnDelete();
            $table->text('catatan')->nullable();
            // Riwayat tidak pernah diubah, jadi hanya ada created_at (ERD v1.1).
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
    }
};
