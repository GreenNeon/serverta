<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Siswa;

$factory->afterCreating(Siswa::class, function ($row, $faker) {
	for ($i = 0; $i < $faker->numberBetween(1, 3); $i++) {
		$row->LinkOrangtua()->attach([
			1 => [
				'fk_orangtua' => $faker->numberBetween(1, 15),
				'fk_siswa' => $faker->numberBetween(1, 15),
				'tinggal_bersama' => $faker->boolean()
			]
		]);
	}
});
