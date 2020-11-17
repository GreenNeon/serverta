<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Resources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
						$table->bigInteger('id');
						$table->string('role');
						$table->string('nama');
						$table->longText('deskripsi');
						$table->morphs('resource');
						$table->primary(['id', 'resource_type', 'resource_id']);
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
        Schema::dropIfExists('resources');
    }
}
