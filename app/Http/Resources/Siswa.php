<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Siswa extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$profile = env('VUE_URL', asset('')) . 'noimg.webp';
		if (!empty($this->avatar()->first())) {
			$id = $this->avatar()->first()->foto_id;
			$path = url('/');
			$profile = "{$path}/api/photos/{$id}";
		}

		return [
			'id' => $this->id,
			'nama' => $this->nama,
			'gender' => $this->gender,
			'nis' => $this->nis,
			'nisn' => $this->nisn,
			'nik' => $this->nik,
			'tempat_lahir' => $this->tempat_lahir,
			'tanggal_lahir' => $this->tanggal_lahir,
			'agama' => $this->agama,
			'kewarganegaraan' => $this->kewarganegaraan,
			'penyakit_berat' => $this->penyakit_berat,
			'golongan_darah' => $this->golongan_darah,
			'kebutuhan_khusus' => $this->kebutuhan_khusus,
			'transportasi' => $this->transportasi,
			'anak_ke' => $this->anak_ke,
			'jumlah_saudara' => $this->jumlah_saudara,
			'no_kps' => $this->no_kps,
			'no_kip' => $this->no_kip,
			'no_kks' => $this->no_kks,
			'reg_akta' => $this->reg_akta,
			'kelas' => new KelasCollection($this->whenLoaded('kelas'), function () {
				return $this->kelas->sortByDesc('jadwal.berhenti');
			}),
			'nilai' => new NilaiCollection($this->whenLoaded('nilai')),
			'tinggal_bersama' => $this->whenPivotLoaded('orangtua_rel_siswa', function () {
				return $this->pivot->tinggal_bersama;
			}),
			'profile_url' => $profile,
			'deleted_at' => $this->deleted_at,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at
		];
	}
}
