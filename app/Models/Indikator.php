<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indikator extends Model
{
	use SoftDeletes;
	protected $table = 'indikator';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = ['fk_pembelajaran'];
	// protected $visible = [];
	// protected $casts = [];

	public function pembelajaran()
	{
		return $this->hasOne('App\Models\Pembelajaran', 'id', 'fk_pembelajaran');
	}

	public function nilai()
	{
		return $this->hasMany('App\Models\Nilai', 'fk_indikator', 'id')->get()->whereNull('deleted_at');
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
