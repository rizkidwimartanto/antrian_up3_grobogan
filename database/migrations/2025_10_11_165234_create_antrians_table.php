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
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->string('layanan'); // contoh: A, B, C, D
            $table->integer('nomor');  // nomor urut per layanan
            $table->date('tanggal');  // tanggal antrian
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'reset_antrian'])->default('menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};
