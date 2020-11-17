<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
	protected $table = 'foto';
	protected $primaryKey = 'foto_id';
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
