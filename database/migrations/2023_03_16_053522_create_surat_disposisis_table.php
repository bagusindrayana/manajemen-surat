<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surat_disposisis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_id')->index();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->unsignedBigInteger('role_id')->index()->nullable();
            $table->unsignedBigInteger('menunggu_persetujuan_id')->index()->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status',['belum','diterima','ditolak'])->default('belum');
            $table->timestamps();

            $table->foreign('surat_id')->references('id')->on('surats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surat_disposisis');
    }
};
