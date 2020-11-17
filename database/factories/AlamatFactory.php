<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Alamat;
use Faker\Generator as Faker;

$factory->define(Alamat::class, function (Faker $faker) {
	return [
		'provinsi' => $faker->state,
		'kabupaten' => $faker->city,
		'kecamatan' => $faker->city,
		'kelurahan' => $faker->streetName,
		'kode_wilayah' => $faker->numerify('######'),
		'kode_pos' => $faker->numerify('#####'),
		'rt' => $faker->numerify('##'),
		'rw' => $faker->numerify('##'),
		'alamat' => $faker->streetAddress
	];
});
