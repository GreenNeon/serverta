<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Kegiatan extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('kegiatan', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('title');
			$table->longText('deskripsi')->nullable();
			$table->unsignedBigInteger('fk_jadwal')->nullable();
			$table->foreign('fk_jadwal')->references('id')->on('jadwal')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
}
