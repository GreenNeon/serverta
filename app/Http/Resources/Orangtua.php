<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Orangtua extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$sources = parent::toArray($request);
		if (empty($sources)) return [];

		if ($this->gender === 'P') $this->gender = 'Perempuan';
		else $this->gender = 'Laki-Laki';
		
		if(!empty($this->pivot)) $sources['tinggal_bersama'] = $this->pivot->tinggal_bersama;
		
		$profile = env('VUE_URL',asset('')) . 'noimg.webp';
		if (!empty($this->avatar()->first())) {
			$id = $this->avatar()->first()->foto_id;
			$path = url('/');
			$profile = "{$path}/api/photos/{$id}";
		}

		return [
			'id' => $this->id,
			'nama' => $this->nama,
			'nik' => $this->nik,
			'gender' => $this->gender,
			'tanggal_lahir' => $this->tanggal_lahir,
			'pendidikan' => $this->pendidikan,
			'pekerjaan' => $this->pekerjaan,
			'penghasilan' => $this->penghasilan,
			'kebutuhan_khusus' => $this->kebutuhan_khusus,
			'telepon' => $this->telepon,
			'smartphone' => $this->smartphone,
			'email' => $this->email,
			'wali' => $this->wali,
			'tinggal_bersama' => $this->whenPivotLoaded('orangtua_rel_siswa', function() {
				return $this->pivot->tinggal_bersama;
			}),
			'alamat' => new Alamat($this->whenLoaded('alamat')),
			'profile_url' => $profile,
			'deleted_at' => $this->deleted_at,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at
		];
	}
}
