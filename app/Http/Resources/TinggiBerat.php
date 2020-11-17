<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TinggiBerat extends JsonResource
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
		$sources['created_at'] = $this->created_at->toFormattedDateString(); 
		return $sources;
	}
}
