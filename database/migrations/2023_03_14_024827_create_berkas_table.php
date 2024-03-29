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
        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_id')->index();
            $table->string('nama_berkas',150);
            $table->string('path',150);
            $table->text('isi_berkas')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('mime_type',100);
            $table->string('size',20);
            $table->timestamps();

            $table->foreign('surat_id')->references('id')->on('surats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
