<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orangtua extends Model
{
	use SoftDeletes;
	protected $table = 'orangtua';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = ['fk_alamat', 'pivot'];
	// protected $visible = [];
	// protected $casts = [];

	public function alamat()
	{
			return $this->hasOne('App\Models\Alamat', 'id', 'fk_alamat');
	}

	public function siswa()
	{
		return $this->belongsToMany('App\Models\Siswa', 'orangtua_rel_siswa', 'fk_orangtua', 'fk_siswa', 'id', 'id')->withPivot('tinggal_bersama');
	}

	public function resources()
	{
		return $this->morphToMany('App\Models\Foto', 'resource','resources','resource_id','id','id','foto_id');
	}

	public function avatar()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources','resource_id','id','id','foto_id')->withPivot('nama','deskripsi','role')->wherePivot('role', 'profile');
	}
}
