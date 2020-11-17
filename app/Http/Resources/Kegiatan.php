<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class Kegiatan extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$now = Carbon::now();
		$profile = env('VUE_URL',asset('')) . 'noimg.webp';
		if (!empty($this->avatar()->first())) {
			$id = $this->avatar()->first()->foto_id;
			$path = url('/');
			$profile = "{$path}/api/photos/{$id}";
		}

		return [
			'id' => $this->id,
			'title' => $this->title,
			'deskripsi' => $this->deskripsi,
			'jadwal' => $this->whenLoaded('jadwal'),
			'aktif' => $this->whenLoaded('jadwal', function () use ($now) {
				if (($this->jadwal->mulai <= $now) && ($this->jadwal->berhenti >= $now)) {
					return true;
				} else return false;
			}),
			'profile_url' => $profile
		];
	}
}
