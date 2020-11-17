<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
	public $timestamps = false;
	protected $table = 'kegiatan';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function jadwal()
	{
		return $this->hasOne('App\Models\Jadwal', 'id', 'fk_jadwal');
	}

	public function avatar()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources', 'resource_id', 'id', 'id', 'foto_id')->withPivot('nama', 'deskripsi', 'role')->wherePivot('role', 'profile');
	}
}
