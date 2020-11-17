<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Nilai extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nilai', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->date('tanggal');
			$table->float('nilai');
			$table->string('catatan')->nullable();
			$table->unsignedBigInteger('fk_indikator');
			$table->foreign('fk_indikator')->references('id')->on('indikator')->onDelete('cascade');
			$table->unsignedBigInteger('fk_siswa');
			$table->foreign('fk_siswa')->references('id')->on('siswa')->onDelete('cascade');
			$table->unsignedBigInteger('fk_kelas');
			$table->foreign('fk_kelas')->references('id')->on('kelas')->onDelete('cascade');
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
		Schema::dropIfExists('nilai');
	}
}
