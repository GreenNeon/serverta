<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class User extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->string('username');
						$table->string('password');
						$table->string('token')->nullable();
						$table->unsignedBigInteger('fk_pegawai')->nullable();
						$table->foreign('fk_pegawai')->references('id')->on('pegawai')->onDelete('cascade');
						$table->unsignedBigInteger('fk_siswa')->nullable();
						$table->foreign('fk_siswa')->references('id')->on('siswa')->onDelete('cascade');
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
        Schema::dropIfExists('user');
    }
}
