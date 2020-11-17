<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SiswaTest extends TestCase
{
	use WithFaker;
	/**
	 * A basic feature test example.
	 *
	 * @return void
	 */
	public function testCreate()
	{
		$response = $this->postJson(
			'/api/siswa',
			[
				'nis' => (string) $this->faker()->unique()->numerify('##########'),
				'nisn' => (string) $this->faker()->unique()->numerify('##########'),
				'nama' => $this->faker()->name,
				'gender' => $this->faker()->randomElement(array('L', 'P')),
				'tanggal_Lahir' => $this->faker()->dateTimeThisDecade,
				'tempat_lahir' => $this->faker()->state,
				'nik' => (string) $this->faker()->unique()->numerify('##########'),
				'jumlah_saudara' => $this->faker()->numberBetween(0, 10),
				'anak_ke' => $this->faker()->numberBetween(1, 10),
				'penyakit_berat' => str_replace(' ', ',', $this->faker()->words(3, true)),
				'transportasi' => str_replace(' ', ',', $this->faker()->words(3, true)),
				'golongan_darah' => $this->faker()->randomElement(array('A', 'AB', 'B', 'O')),
				'kewarganegaraan' => $this->faker()->country,
				'kebutuhan_khusus' => str_replace(' ', ',', $this->faker()->words(3, true)),
				'agama' => $this->faker()->randomElement(array('Katholik', 'Kristen', 'Islam', 'Budha', 'Khonghucu', 'Hindu')),
				'no_kps' => $this->faker()->unique()->numerify('#########'),
				'no_kip' => $this->faker()->unique()->numerify('#########'),
				'no_kks' => $this->faker()->unique()->numerify('#########'),
				'reg_akta' => $this->faker()->unique()->numerify('#########'),
			]
		);

		$response->assertStatus(200);
	}
}
