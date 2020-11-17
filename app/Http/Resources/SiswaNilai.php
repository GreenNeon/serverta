<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SiswaNilai extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		if ($this->gender === 'P') $sources['gender'] = 'Perempuan';
		else $sources['gender'] = 'Laki-laki';

		$sources['profile_url'] = env('VUE_URL',asset('')) . 'noimg.webp';
		if (!empty($this->avatar()->first())) {
			$id = $this->avatar()->first()->foto_id;
			$path = url('/');
			$sources['profile_url'] = "{$path}/api/photos/{$id}";
		}
		$fk_kelas = $this->pivot->fk_kelas;
		$sources = array_merge($sources, [
			'id' => $this->id,
			'nama' => $this->nama,
			'nis' => $this->nis,
			'tanggal_lahir' => (new Carbon($this->tanggal_lahir))->locale('id')->formatLocalized('%d %b, %G'),
			'nilai' => $this->nilai()->where('fk_kelas', $fk_kelas)->get()
		]);
		return $sources;
	}
}
