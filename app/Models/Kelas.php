<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Kelas extends Model
{
	use SoftDeletes;
	protected $table = 'kelas';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = ['fk_pegawai','fk_jadwal'];
	// protected $visible = [];
	// protected $casts = [];

	public function pegawai()
	{
		return $this->hasOne('App\Models\Pegawai', 'id', 'fk_pegawai');
	}

	public function jadwal()
	{
		return $this->hasOne('App\Models\Jadwal', 'id', 'fk_jadwal');
	}
	
	public function jadwalStatus()
	{
		$now = Carbon::now();
		return $this->hasOne('App\Models\Jadwal', 'id', 'fk_jadwal')->where([
			['mulai', '<=', $now],
			['berhenti', '>=', $now]
		]);
	}

	public function siswa()
	{
		return $this->belongsToMany('App\Models\Siswa', 'kelas_rel_siswa', 'fk_kelas', 'fk_siswa', 'id', 'id')->withPivot('fk_kelas')->orderBy('nama');
	}

	public function pembelajaran()
	{
		return $this->belongsToMany('App\Models\Pembelajaran', 'kelas_rel_pembelajaran', 'fk_kelas', 'fk_pembelajaran', 'id', 'id')->withPivot('tanggal');
	}
	
	public function avatar()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources','resource_id','id','id','foto_id')->withPivot('nama','deskripsi','role')->wherePivot('role', 'profile');
	}

	public function album()
	{
		return $this->hasOne('App\Models\Album', 'fk_kelas', 'id');
	}

	
	/**
     * Get the event
     *
     * @return bool
     */
    public function getKegiatanAttribute($value)
    {
			return $value;
		}
		
		/**
     * Set event
     *
     * @param  string  $value
     * @return void
     */
    public function setKegiatanAttribute($value)
    {
			$this->attributes['kegiatan'] = $value;
    }
}
