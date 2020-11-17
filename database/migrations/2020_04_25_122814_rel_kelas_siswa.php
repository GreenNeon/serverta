<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RelKelasSiswa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kelas_rel_siswa', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->unsignedBigInteger('fk_kelas');
						$table->unsignedBigInteger('fk_siswa');
						$table->foreign('fk_siswa')->references('id')->on('siswa')->onDelete('cascade');
						$table->foreign('fk_kelas')->references('id')->on('kelas')->onDelete('cascade');
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
			Schema::dropIfExists('kelas_rel_siswa');
    }
}
