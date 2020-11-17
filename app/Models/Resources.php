<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resources extends Model
{
	protected $table = 'resources';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function siswa()
	{
		return $this->morphedByMany('App\Models\Siswa', 'resource');
	}
}
