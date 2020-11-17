<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
	protected $table = 'alamat';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function pegawai()
	{
			return $this->belongsTo('App\Models\Pegawai', 'fk_alamat', 'id');
	}

	public function orangtua()
	{
			return $this->belongsTo('App\Models\Orangtua', 'fk_alamat', 'id');
	}
}
