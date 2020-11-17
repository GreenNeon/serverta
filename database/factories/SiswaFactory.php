<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Siswa;
use Faker\Generator as Faker;

$factory->define(Siswa::class, function (Faker $faker) {
	return [
		'nis' => (string) $faker->unique()->numerify('##########'),
		'nisn' => (string) $faker->unique()->numerify('##########'),
		'nama' => $faker->name,
		'gender' => $faker->randomElement(array('L', 'P')),
		'tanggal_Lahir' => $faker->dateTimeThisDecade,
		'tempat_lahir' => $faker->state,
		'nik' => (string) $faker->unique()->numerify('##########'),
		'jumlah_saudara' => $faker->numberBetween(0, 10),
		'anak_ke' => $faker->numberBetween(1, 10),
		'penyakit_berat' => str_replace(' ', ',', $faker->words(3, true)),
		'transportasi' => str_replace(' ', ',', $faker->words(3, true)),
		'golongan_darah' => $faker->randomElement(array('A', 'AB', 'B', 'O')),
		'kewarganegaraan' => $faker->country,
		'kebutuhan_khusus' => str_replace(' ', ',', $faker->words(3, true)),
		'agama' => $faker->randomElement(array('Katholik', 'Kristen', 'Islam', 'Budha', 'Khonghucu', 'Hindu')),
		'no_kps' => $faker->unique()->numerify('#########'),
		'no_kip' => $faker->unique()->numerify('#########'),
		'no_kks' => $faker->unique()->numerify('#########'),
		'reg_akta' => $faker->unique()->numerify('#########'),
	];
});
