<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Blog extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$url = env('VUE_URL',asset('')) . 'noimg.webp';
		if (!empty($this->image()->first())) {
			$id = $this->image()->first()->foto_id;
			$path = url('/');
			$url = "{$path}/api/photos/{$id}";
		}
		return [
			'id' => $this->id,
			'title' => $this->title,
			'subtitle' => $this->subtitle,
			'body' => $this->body,
			'image' => $url,
			'created_at' => $this->created_at
		];
	}
}
