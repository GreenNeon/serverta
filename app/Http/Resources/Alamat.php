<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Alamat extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$alamat = trim($this->alamat ?? '??');
		$rt = trim($this->rt ?? '??');
		$rw = trim($this->rw ?? '??');
		$kel = trim($this->kelurahan ?? '??');
		$kab = trim($this->kabupaten ?? '??');
		$kec = trim($this->kecamatan  ?? '??');
		$prov = trim($this->provinsi  ?? '??');
		
		return [
			'id' => $this->id,
			'merged' => "{$alamat} 
					\n {$rt}/{$rw} {$kel} \n
					{$kec}, {$kab} \n 
					{$prov}",
			'alamat' => $this->alamat,
			'rt' => $this->rt,
			'rw' => $this->rw,
			'provinsi' => $this->provinsi,
			'kabupaten' => $this->kabupaten,
			'kecamatan' => $this->kecamatan,
			'kode_wilayah' => $this->kode_wilayah,
			'kode_pos' => $this->kode_pos,
			'kelurahan' => $this->kelurahan
		];
	}
}
