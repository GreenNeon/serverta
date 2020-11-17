<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelajaran extends Model
{
	use SoftDeletes;
	protected $table = 'pembelajaran';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function kelas()
	{
		return $this->belongsToMany('App\Models\Kelas', 'kelas_rel_pembelajaran', 'fk_pembelajaran', 'fk_kelas', 'id', 'id');
	}

	public function indikator()
	{
		return $this->hasMany('App\Models\Indikator', 'fk_pembelajaran', 'id');
	}

	public function resources()
	{
		return $this->morphToMany('App\Models\Resources', 'resource');
	}
	
	public function avatar()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources','resource_id','id','id','foto_id')->withPivot('nama','deskripsi','role')->wherePivot('role', 'profile');
	}
}
