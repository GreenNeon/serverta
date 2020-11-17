<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CatatanAnekdot extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$profile = env('VUE_URL',asset('')) . 'noimg.webp';
		if (!empty($this->avatar()->first())) {
			$id = $this->avatar()->first()->foto_id;
			$path = url('/');
			$profile = "{$path}/api/photos/{$id}";
		}

		return [
			'id' => $this->id,
			'siswa' => $this->whenLoaded('siswa'),
			'kelas' => $this->whenLoaded('kelas'),
			'tanggal' => $this->tanggal,
			'peristiwa' => $this->peristiwa,
			'evaluasi' => $this->evaluasi,
			'profile_url' => $profile,
			'keterangan' => $this->keterangan,
			'deleted_at' => $this->deleted_at,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at
		];
	}
}
