<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class Album extends JsonResource
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

		$sources['photos'] = new FotoCollection($this->photos()->simplePaginate(5));
		return $sources;
	}
}
