<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Kelas extends JsonResource
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
		$now = Carbon::now();

		return [
			'id' => $this->id,
			'nama' => $this->nama,
			'kelompok' => $this->kelompok,
			'jadwal' => new Jadwal($this->whenLoaded('jadwal')),
			'pegawai' => new Pegawai($this->whenLoaded('pegawai')),
			'kalender' => new PembelajaranCollection($this->whenLoaded('pembelajaran')),
			'profile_url' => $profile,
			'aktif' => $this->whenLoaded('jadwal', function () use ($now) {
				if (($this->jadwal->mulai <= $now) && ($this->jadwal->berhenti >= $now)) {
					return true;
				} else return false;
			}),
			'deleted_at' => $this->deleted_at,
			'updated_at' => $this->updated_at,
			'created_at' => $this->created_at
		];
	}
}
