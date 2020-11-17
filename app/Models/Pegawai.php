<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
	use SoftDeletes;
	protected $table = 'pegawai';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = ['fk_alamat'];
	// protected $visible = [];
	// protected $casts = [];

	public function alamat()
	{
			return $this->hasOne('App\Models\Alamat', 'id', 'fk_alamat');
	}

	public function avatar()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources','resource_id','id','id','foto_id')->withPivot('nama','deskripsi','role')->wherePivot('role', 'profile');
	}
	public function user()
	{
		return $this->hasOne('App\User', 'fk_pegawai', 'id');
	}
}
