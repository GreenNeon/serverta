<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Album extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('album', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('title');
			$table->longText('deskripsi');
			$table->unsignedBigInteger('fk_kelas')->nullable();
			$table->foreign('fk_kelas')->references('id')->on('kelas')->onDelete('set null');
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
