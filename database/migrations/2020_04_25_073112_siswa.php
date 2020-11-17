<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Siswa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('siswa', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->string('nama');
						$table->string('gender',1);
						$table->string('nis')->unique()->nullable();
						$table->string('nisn')->unique();
						$table->string('nik');
						$table->string('tempat_lahir');
						$table->date('tanggal_lahir');
						$table->string('agama',15);
						$table->string('kewarganegaraan');
						$table->string('penyakit_berat')->nullable();
						$table->string('golongan_darah',2);
						$table->string('kebutuhan_khusus')->nullable();
						$table->string('transportasi');
						$table->tinyInteger('anak_ke');
						$table->tinyInteger('jumlah_saudara');
						$table->string('no_kps')->nullable();
						$table->string('no_kip')->nullable();
						$table->string('no_kks')->nullable();
						$table->string('reg_akta')->nullable();
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
        Schema::dropIfExists('siswa');
    }
}
