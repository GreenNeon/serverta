<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Pembelajaran extends JsonResource
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
			'tanggal' => $this->whenPivotLoaded('kelas_rel_pembelajaran', function () {
				return $this->pivot->tanggal;
			}),
			$this->mergeWhen(!$this->whenPivotLoaded('kelas_rel_pembelajaran', function () {
				return true;
			}, false), [
				'indikator_count' => $this->whenLoaded('indikator', function () {
					return $this->indikator->count();
				}),
				'profile_url' => $profile,
				'deleted_at' => $this->deleted_at,
				'updated_at' => $this->updated_at,
				'created_at' => $this->created_at
			]),
		];
	}
}
