<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RelKelasPembelajaran extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kelas_rel_pembelajaran', function (Blueprint $table) {
						$table->bigIncrements('id');
						$table->date('tanggal');
						$table->unsignedBigInteger('fk_kelas');
						$table->unsignedBigInteger('fk_pembelajaran');
						$table->foreign('fk_pembelajaran')->references('id')->on('pembelajaran')->onDelete('cascade');
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
        //
    }
}
