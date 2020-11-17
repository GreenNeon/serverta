<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Pegawai;
use Faker\Generator as Faker;

$factory->define(Pegawai::class, function (Faker $faker) {
	return [		
		'nip' => (string) $faker->unique()->numerify('###.######'),
		'nik' => (string) $faker->unique()->numerify('##########'),
		'nama' => $faker->name,
		'role' => $faker->randomElement(array('PG', 'GU', 'OR')),
		'gender' => $faker->randomElement(array('laki-laki', 'perempuan')),
		'smartphone' => $faker->phoneNumber,
		'telephone' => $faker->e164PhoneNumber,
		'email' => $faker->email,
		'tanggal_Lahir' => $faker->dateTimeThisDecade,
		'fk_alamat' => $faker->numberBetween(1,15),
	];
});
 