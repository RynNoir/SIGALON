<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuat ulang tabel `transaksi` sesuai ERD v1.1 (TRANSAKSI).
 *
 * Tabel versi fondasi belum pernah diisi oleh fitur apa pun (pencatatan transaksi dimulai di Sprint 2),
 * sehingga aman dibuat ulang. Satu baris = satu jenis item (PRD §6).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('transaksi');

        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan')->nullOnDelete();
            $table->foreignId('kurir_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('jenis_kanal', 20);
            $table->string('jenis_item', 20);
            $table->unsignedInteger('jumlah_galon');
            // Rupiah tanpa sen; disimpan per transaksi agar histori tidak berubah saat harga diubah (PRD §6).
            $table->unsignedBigInteger('total_harga');
            $table->string('metode_bayar', 10);
            $table->string('status', 30);
            $table->date('tanggal');
            $table->string('nama_pemesan')->nullable();
            $table->string('no_hp_pemesan', 20)->nullable();
            $table->text('alamat_antar')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['cabang_id', 'tanggal']);
            $table->index(['jenis_kanal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');

        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();
            $table->string('kanal', 20);
            $table->string('jenis_item', 30);
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('harga_satuan');
            $table->unsignedBigInteger('total');
            $table->string('metode_pembayaran', 10);
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();

            $table->index(['cabang_id', 'dibayar_pada']);
            $table->index(['kanal', 'dibayar_pada']);
        });
    }
};
