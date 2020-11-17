<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Indikator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indikator', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->string('nama');
						$table->string('deskripsi')->nullable();
						$table->unsignedBigInteger('fk_pembelajaran');
						$table->foreign('fk_pembelajaran')->references('id')->on('pembelajaran')->onDelete('cascade');
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
        Schema::dropIfExists('indikator');
    }
}
