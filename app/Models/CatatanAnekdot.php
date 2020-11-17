<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatatanAnekdot extends Model
{
	use SoftDeletes;
	protected $table = 'catatan_anekdot';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = ['fk_siswa', 'fk_kelas'];
	// protected $visible = [];
	// protected $casts = [];

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

	
	public function avatar()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources','resource_id','id','id','foto_id')->withPivot('nama','deskripsi','role')->wherePivot('role', 'profile');
	}
}
