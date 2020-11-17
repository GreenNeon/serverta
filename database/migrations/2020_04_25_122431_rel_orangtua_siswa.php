<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RelOrangtuaSiswa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orangtua_rel_siswa', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->unsignedBigInteger('fk_orangtua');
						$table->unsignedBigInteger('fk_siswa');
						$table->foreign('fk_orangtua')->references('id')->on('orangtua')->onDelete('cascade');
						$table->foreign('fk_siswa')->references('id')->on('siswa')->onDelete('cascade');
						$table->boolean('tinggal_bersama');
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
        Schema::dropIfExists('orangtua_rel_siswa');
    }
}
