<?php

use App\Models\Alamat;
use App\Models\Orangtua;
use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\SiswaRelOrangtua;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run()
	{
		factory(Alamat::class, 15)->create();
		factory(Orangtua::class, 15)->create();
		factory(Siswa::class, 15)->create();
	}
}
