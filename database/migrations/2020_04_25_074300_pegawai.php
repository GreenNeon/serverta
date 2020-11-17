<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Pegawai extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pegawai', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('nama');
			$table->string('nik');
			$table->string('nip');
			$table->string('role', 2);
			$table->string('gender', 1);
			$table->date('tanggal_lahir');
			$table->string('telepon')->nullable();
			$table->string('smartphone')->nullable();
			$table->string('email')->nullable();
			$table->unsignedBigInteger('fk_alamat')->nullable();
			$table->foreign('fk_alamat')->references('id')->on('alamat')->onDelete('cascade');
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
		Schema::dropIfExists('pegawai');
	}
}
