<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FotoCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
			return [
				'data' => $this->collection,
				'meta' => [
					'has_more' => $this->hasMorePages(),
					'page' => $this->currentPage()
				],
		];
    }
}
