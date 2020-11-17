<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Orangtua;
use Faker\Generator as Faker;

$factory->define(Orangtua::class, function (Faker $faker) {
	return [
		'nik' => (string) $faker->unique()->numerify('##########'),
		'nama' => $faker->name,
		'gender' => $faker->randomElement(array('L', 'P')),
		'smartphone' => $faker->phoneNumber,
		'telepon' => $faker->e164PhoneNumber,
		'email' => $faker->email,
		'tanggal_Lahir' => $faker->dateTimeThisDecade,
		'fk_alamat' => $faker->numberBetween(1, 15),
		'pekerjaan' => $faker->word(),
		'pendidikan' => $faker->company,
		'wali' => $faker->boolean(),
		'penghasilan' => $faker->randomNumber(),
		'kebutuhan_khusus' => str_replace(' ', ',', $faker->words(3, true))
	];
});
