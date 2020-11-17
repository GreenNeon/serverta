<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Siswa extends Model
{
	use SoftDeletes;
	protected $table = 'siswa';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function orangtua()
	{
		return $this->belongsToMany('App\Models\Orangtua', 'orangtua_rel_siswa', 'fk_siswa', 'fk_orangtua', 'id', 'id')->withPivot('tinggal_bersama');
	}

	public function tinggiberat()
	{
		return $this->belongsToMany('App\Models\TinggiBerat', 'tinggi_berat_rel_siswa', 'fk_siswa', 'fk_tinggi_berat', 'id', 'id');
	}

	public function kelas()
	{
		return $this->belongsToMany('App\Models\Kelas', 'kelas_rel_siswa', 'fk_siswa', 'fk_kelas', 'id', 'id');
	}

	public function kelasByLast()
	{
		return $this->belongsToMany('App\Models\Kelas', 'kelas_rel_siswa', 'fk_siswa', 'fk_kelas', 'id', 'id')->with('jadwal')->get()->sortByDesc('jadwal.berhenti');
	}

	public function kelasByStatus()
	{
		$now = Carbon::now();
		return $this->belongsToMany('App\Models\Kelas', 'kelas_rel_siswa', 'fk_siswa', 'fk_kelas', 'id', 'id')->with('jadwal')->whereHas('jadwal', function($q) use($now){
			$q->where([
				['mulai', '<=', $now],
				['berhenti', '>=', $now]
			]);
		});
	}

	public function resources()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources', 'resource_id', 'id', 'id', 'foto_id');
	}

	public function nilai()
	{
		return $this->hasMany('App\Models\Nilai', 'fk_siswa', 'id');
	}

	public function catatan()
	{
		return $this->hasMany('App\Models\CatatanAnekdot', 'fk_siswa', 'id');
	}

	public function avatar()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources', 'resource_id', 'id', 'id', 'foto_id')->withPivot('nama', 'deskripsi', 'role')->wherePivot('role', 'profile');
	}

	public function user()
	{
		return $this->hasOne('App\User', 'fk_siswa', 'id');
	}
	
}
