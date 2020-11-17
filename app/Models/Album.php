<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
	public $timestamps = false;
	protected $table = 'album';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function jadwal()
	{
		return $this->hasOne('App\Models\Kelas', 'id', 'fk_kelas');
	}

	public function photos()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources','resource_id','id','id','foto_id')->withPivot('nama','deskripsi','role')->wherePivot('role', 'album');
	}
}
