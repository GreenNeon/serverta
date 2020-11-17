<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CatatanAnekdot extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('catatan_anekdot', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('fk_siswa');
			$table->foreign('fk_siswa')->references('id')->on('siswa')->onDelete('cascade');
			$table->unsignedBigInteger('fk_kelas');
			$table->foreign('fk_kelas')->references('id')->on('kelas')->onDelete('cascade');
			$table->date('tanggal');
			$table->longText('peristiwa');
			$table->longText('evaluasi')->nullable();
			$table->longText('keterangan')->nullable();
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
		Schema::dropIfExists('catatan_anekdot');
	}
}
