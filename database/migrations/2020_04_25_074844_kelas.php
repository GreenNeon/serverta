<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Kelas extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('kelas', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('nama');
			$table->string('kelompok', 1);
			$table->unsignedBigInteger('fk_pegawai')->nullable();
			$table->foreign('fk_pegawai')->references('id')->on('pegawai')->onDelete('set null');
			$table->unsignedBigInteger('fk_jadwal')->nullable();
			$table->foreign('fk_jadwal')->references('id')->on('jadwal')->onDelete('cascade');
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('kelas');
	}
}
