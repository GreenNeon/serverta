<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Indikator extends JsonResource
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
			'deskripsi' => $this->deskripsi,
			'pembelajaran' => $this->whenLoaded('pembelajaran'),
			'profile_url' => $profile,
			'deleted_at' => $this->deleted_at,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at
		];
	}
}
