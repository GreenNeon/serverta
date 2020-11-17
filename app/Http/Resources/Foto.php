<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Foto extends JsonResource
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

		$sources['url'] = env('VUE_URL',asset('')) . 'noimg.webp';
		if (!empty($this->file_path)) {
			$path = url('/');
			$sources['url'] = "{$path}/api/photos/{$this->foto_id}";
			unset($sources['file_path']);
			unset($sources['id']);
		}

		return $sources;
	}
}
