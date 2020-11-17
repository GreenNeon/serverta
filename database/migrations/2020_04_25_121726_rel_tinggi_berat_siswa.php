<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RelTinggiBeratSiswa extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tinggi_berat_rel_siswa', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('fk_tinggi_berat');
			$table->unsignedBigInteger('fk_siswa');
			$table->foreign('fk_tinggi_berat')->references('id')->on('tinggi_berat')->onDelete('cascade');
			$table->foreign('fk_siswa')->references('id')->on('siswa')->onDelete('cascade');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('tinggi_berat_rel_siswa');
	}
}
