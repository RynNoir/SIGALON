<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Satu baris = satu jenis item. Penggabungan air isi ulang dan galon kosong
     * dalam satu transaksi belum diputuskan (PRD §15 no. 1).
     */
    public function up(): void
    {
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
            // Pendapatan dihitung dari transaksi yang pembayarannya sudah diterima (PRD §7.H).
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();

            $table->index(['cabang_id', 'dibayar_pada']);
            $table->index(['kanal', 'dibayar_pada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
