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
        Schema::create('berkas_storages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('berkas_id')->index();
            $table->unsignedBigInteger('storage_id')->index();
            $table->string('path');
            $table->timestamps();

            $table->foreign('berkas_id')->references('id')->on('berkas')->onDelete('cascade');
            $table->foreign('storage_id')->references('id')->on('cloud_storages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('berkas_storages');
    }
};
