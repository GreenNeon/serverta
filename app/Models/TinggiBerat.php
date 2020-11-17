<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TinggiBerat extends Model
{
	protected $table = 'tinggi_berat';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function siswa()
	{
		return $this->belongsToMany('App\Models\Siswa', 'tinggi_berat_rel_siswa', 'fk_tinggi_berat', 'fk_siswa', 'id', 'id');
	}
}
