<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Orangtua extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orangtua', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->string('nama');
						$table->string('nik');
						$table->string('gender',1);
						$table->date('tanggal_lahir');
						$table->string('pendidikan')->nullable();
						$table->string('pekerjaan')->nullable();
						$table->string('penghasilan')->nullable();
						$table->string('kebutuhan_khusus')->nullable();
						$table->string('telepon')->nullable();
						$table->string('smartphone');
						$table->string('email')->nullable();
						$table->boolean('wali');
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
        Schema::dropIfExists('orangtua');
    }
}
