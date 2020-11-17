<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alamat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alamat', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->longText('alamat')->nullable();
						$table->string('rt', 12)->nullable();
						$table->string('rw', 12)->nullable();
						$table->string('provinsi')->nullable();
						$table->string('kabupaten')->nullable();
						$table->string('kecamatan')->nullable();
						$table->string('kode_wilayah')->nullable();
						$table->string('kode_pos',5)->nullable();
						$table->string('kelurahan')->nullable();
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
        Schema::dropIfExists('alamat');
    }
}
