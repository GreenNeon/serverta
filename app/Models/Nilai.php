<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nilai extends Model
{
	use SoftDeletes;
	protected $table = 'nilai';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = ['deleted_at', 'updated_at'];
	// protected $visible = [];
	// protected $casts = [];

	public function indikator()
	{
		return $this->hasOne('App\Models\Indikator', 'id', 'fk_indikator');
	}

	public function siswa()
	{
		return $this->hasOne('App\Models\Siswa', 'id', 'fk_siswa');
	}

	public function kelas()
	{
		return $this->hasOne('App\Models\Kelas', 'id', 'fk_kelas');
	}

	public function resources()
	{
		return $this->morphToMany('App\Models\Resources', 'resource');
	}
}
