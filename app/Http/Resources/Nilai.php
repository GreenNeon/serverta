<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Nilai extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'nilai' => $this->nilai,
			'catatan' => $this->catatan,
			'indikator' => $this->whenLoaded('indikator'),
			'siswa' => $this->whenLoaded('siswa'),
			'kelas' => $this->whenLoaded('kelas'),
			'deleted_at' => $this->deleted_at,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at
		];
	}
}
